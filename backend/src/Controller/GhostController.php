<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\GhostRound;
use App\Entity\GhostVote;
use App\Entity\User;
use App\Repository\CharactersRepository;
use App\Repository\GhostRoundRepository;
use App\Repository\GhostVoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GhostController extends AbstractController
{
    private const DEFAULT_THRESHOLD = 4;

    #[Route('/ghost/current', name: 'ghost_current', methods: ['GET'])]
    public function getCurrent(
        GhostRoundRepository $roundRepository,
        GhostVoteRepository $voteRepository,
        CharactersRepository $charactersRepository,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        $round = $this->getOrCreateOpenRound($roundRepository, $em);
        $votes = $voteRepository->findByRound($round);

        $byCharacter = [];
        foreach ($votes as $vote) {
            // A character archived after being nominated (e.g. via the exclusion shortcut)
            // shouldn't linger in the active list — its votes still count for history once the round closes.
            if ($vote->getCharacter()->isArchived()) {
                continue;
            }
            $characterId = $vote->getCharacter()->getId();
            $byCharacter[$characterId] ??= ['character' => $vote->getCharacter(), 'votes' => []];
            $byCharacter[$characterId]['votes'][] = $vote;
        }

        $nominees = [];
        foreach ($byCharacter as $entry) {
            $character = $entry['character'];
            $voters = array_values(array_map(
                fn($vote) => $this->formatVoter($vote->getVoter(), $charactersRepository),
                $entry['votes']
            ));
            $hasVoted = $user && in_array($user->getId(), array_column($voters, 'id'), true);
            $breakdown = $this->buildRoundBreakdown($character, $voteRepository, $charactersRepository);
            $timesReached = count(array_filter($breakdown, fn($r) => $r['thresholdReached']));

            $nominees[] = array_merge($this->formatCharacterSummary($character), [
                'voteCount' => count($entry['votes']),
                'voters' => $voters,
                'hasVoted' => $hasVoted,
                'timesThresholdReached' => $timesReached,
                'eligibleExclusion' => $timesReached >= 2,
            ]);
        }

        return $this->json([
            'round' => [
                'id' => $round->getId(),
                'openedAt' => $round->getOpenedAt()->format('Y-m-d'),
                'threshold' => $round->getThreshold(),
            ],
            'nominees' => $nominees,
        ]);
    }

    #[Route('/ghost/characters/{id}/vote', name: 'ghost_vote', methods: ['POST'])]
    public function vote(
        int $id,
        CharactersRepository $charactersRepository,
        GhostRoundRepository $roundRepository,
        GhostVoteRepository $voteRepository,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Authentication required.'], 403);
        }

        $character = $charactersRepository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
        if ($character->isArchived()) {
            return $this->json(['error' => 'Archived characters cannot be nominated.'], 400);
        }

        $round = $this->getOrCreateOpenRound($roundRepository, $em);

        $existing = $voteRepository->findOneVote($round, $character, $user);
        if (!$existing) {
            $vote = new GhostVote();
            $vote->setRound($round)->setCharacter($character)->setVoter($user);
            $voteRepository->add($vote, true);
        }

        return $this->json($this->formatNomineeState($character, $round, $voteRepository, $charactersRepository, $user));
    }

    #[Route('/ghost/characters/{id}/vote', name: 'ghost_unvote', methods: ['DELETE'])]
    public function unvote(
        int $id,
        CharactersRepository $charactersRepository,
        GhostRoundRepository $roundRepository,
        GhostVoteRepository $voteRepository,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return $this->json(['error' => 'Authentication required.'], 403);
        }

        $character = $charactersRepository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $round = $roundRepository->findOpenRound();
        if ($round) {
            $existing = $voteRepository->findOneVote($round, $character, $user);
            if ($existing) {
                $voteRepository->remove($existing, true);
            }
        }

        return $this->json($round
            ? $this->formatNomineeState($character, $round, $voteRepository, $charactersRepository, $user)
            : ['id' => $character->getId(), 'voteCount' => 0, 'voters' => [], 'hasVoted' => false]);
    }

    #[Route('/ghost/threshold', name: 'ghost_update_threshold', methods: ['PUT'])]
    public function updateThreshold(
        Request $request,
        GhostRoundRepository $roundRepository,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if ($err = $this->requireOwner($user)) {
            return $err;
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['threshold']) || !is_int($data['threshold']) || $data['threshold'] < 1) {
            return $this->json(['error' => 'threshold must be a positive integer'], 400);
        }

        $round = $this->getOrCreateOpenRound($roundRepository, $em);
        $round->setThreshold($data['threshold']);
        $em->flush();

        return $this->json([
            'id' => $round->getId(),
            'openedAt' => $round->getOpenedAt()->format('Y-m-d'),
            'threshold' => $round->getThreshold(),
        ]);
    }

    #[Route('/ghost/close', name: 'ghost_close', methods: ['POST'])]
    public function closeRound(
        GhostRoundRepository $roundRepository,
        GhostVoteRepository $voteRepository,
        EntityManagerInterface $em,
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if ($err = $this->requireOwner($user)) {
            return $err;
        }

        $round = $roundRepository->findOpenRound();
        if (!$round) {
            return $this->json(['error' => 'No open round to close.'], 400);
        }

        $votes = $voteRepository->findByRound($round);
        $byCharacter = [];
        foreach ($votes as $vote) {
            $characterId = $vote->getCharacter()->getId();
            $byCharacter[$characterId] ??= ['character' => $vote->getCharacter(), 'count' => 0];
            $byCharacter[$characterId]['count']++;
        }

        $reachedThreshold = [];
        foreach ($byCharacter as $entry) {
            if ($entry['count'] >= $round->getThreshold()) {
                $reachedThreshold[] = [
                    'characterId' => $entry['character']->getId(),
                    'pseudo' => $entry['character']->getPseudo(),
                    'voteCount' => $entry['count'],
                ];
            }
        }

        $round->setClosedAt(new \DateTime());
        $round->setClosedBy($user);

        $newRound = new GhostRound();
        $newRound->setThreshold($round->getThreshold());
        $em->persist($newRound);
        $em->flush();

        return $this->json([
            'closedRound' => [
                'id' => $round->getId(),
                'openedAt' => $round->getOpenedAt()->format('Y-m-d'),
                'closedAt' => $round->getClosedAt()->format('Y-m-d'),
                'threshold' => $round->getThreshold(),
            ],
            'reachedThreshold' => $reachedThreshold,
            'newRound' => [
                'id' => $newRound->getId(),
                'openedAt' => $newRound->getOpenedAt()->format('Y-m-d'),
                'threshold' => $newRound->getThreshold(),
            ],
        ]);
    }

    #[Route('/ghost/characters/{id}/history', name: 'ghost_character_history', methods: ['GET'])]
    public function history(
        int $id,
        CharactersRepository $charactersRepository,
        GhostVoteRepository $voteRepository
    ): JsonResponse {
        $character = $charactersRepository->find($id);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }

        $breakdown = $this->buildRoundBreakdown($character, $voteRepository, $charactersRepository);
        $timesReached = count(array_filter($breakdown, fn($r) => $r['thresholdReached']));

        return $this->json([
            'characterId' => $character->getId(),
            'rounds' => $breakdown,
            'timesThresholdReached' => $timesReached,
            'eligibleExclusion' => $timesReached >= 2,
        ]);
    }

    #[Route('/ghost/totals', name: 'ghost_totals', methods: ['GET'])]
    public function getTotals(GhostVoteRepository $voteRepository): JsonResponse
    {
        return $this->json($voteRepository->countAllByCharacter());
    }

    #[Route('/ghost/registry', name: 'ghost_registry', methods: ['GET'])]
    public function getRegistry(GhostVoteRepository $voteRepository): JsonResponse
    {
        $votes = $voteRepository->findAllWithCharacterAndRound();

        $byCharacter = [];
        foreach ($votes as $vote) {
            $character = $vote->getCharacter();
            $characterId = $character->getId();
            $byCharacter[$characterId] ??= ['character' => $character, 'byRound' => []];
            $round = $vote->getRound();
            $roundId = $round->getId();
            $byCharacter[$characterId]['byRound'][$roundId] ??= ['round' => $round, 'count' => 0];
            $byCharacter[$characterId]['byRound'][$roundId]['count']++;
        }

        $result = [];
        foreach ($byCharacter as $entry) {
            $character = $entry['character'];
            $totalVotes = 0;
            $timesThresholdReached = 0;
            $currentRoundVoteCount = 0;
            $lastFlaggedAt = null;

            foreach ($entry['byRound'] as $rc) {
                $totalVotes += $rc['count'];
                if ($rc['round']->getClosedAt() !== null) {
                    if ($rc['count'] >= $rc['round']->getThreshold()) {
                        $timesThresholdReached++;
                        if (!$lastFlaggedAt || $rc['round']->getClosedAt() > $lastFlaggedAt) {
                            $lastFlaggedAt = $rc['round']->getClosedAt();
                        }
                    }
                } else {
                    $currentRoundVoteCount = $rc['count'];
                }
            }

            $result[] = array_merge($this->formatCharacterSummary($character), [
                'isArchived' => $character->isArchived(),
                'totalVotes' => $totalVotes,
                'currentRoundVoteCount' => $currentRoundVoteCount,
                'timesThresholdReached' => $timesThresholdReached,
                'eligibleExclusion' => $timesThresholdReached >= 2,
                'lastFlaggedAt' => $lastFlaggedAt?->format('Y-m-d'),
            ]);
        }

        usort($result, function ($a, $b) {
            return $b['timesThresholdReached'] <=> $a['timesThresholdReached']
                ?: $b['totalVotes'] <=> $a['totalVotes'];
        });

        return $this->json($result);
    }

    #[Route('/ghost/rounds', name: 'ghost_rounds_list', methods: ['GET'])]
    public function listClosedRounds(
        GhostRoundRepository $roundRepository,
        GhostVoteRepository $voteRepository
    ): JsonResponse {
        $rounds = $roundRepository->findClosedRounds();

        $result = array_map(function (GhostRound $round) use ($voteRepository) {
            $votes = $voteRepository->findByRound($round);
            $byCharacter = [];
            foreach ($votes as $vote) {
                $characterId = $vote->getCharacter()->getId();
                $byCharacter[$characterId] ??= ['character' => $vote->getCharacter(), 'count' => 0];
                $byCharacter[$characterId]['count']++;
            }

            $reached = [];
            foreach ($byCharacter as $entry) {
                if ($entry['count'] >= $round->getThreshold()) {
                    $reached[] = [
                        'characterId' => $entry['character']->getId(),
                        'pseudo' => $entry['character']->getPseudo(),
                        'voteCount' => $entry['count'],
                    ];
                }
            }

            return [
                'id' => $round->getId(),
                'openedAt' => $round->getOpenedAt()->format('Y-m-d'),
                'closedAt' => $round->getClosedAt()?->format('Y-m-d'),
                'threshold' => $round->getThreshold(),
                'nomineeCount' => count($byCharacter),
                'reachedThresholdCount' => count($reached),
                'reachedThreshold' => $reached,
            ];
        }, $rounds);

        return $this->json($result);
    }

    private function requireOwner(?User $user): ?JsonResponse
    {
        if (!$user || !in_array('ROLE_OWNERS', $user->getRoles())) {
            return $this->json(['error' => 'Access denied. Only Owners can perform this action.'], 403);
        }
        return null;
    }

    private function getOrCreateOpenRound(GhostRoundRepository $roundRepository, EntityManagerInterface $em): GhostRound
    {
        $round = $roundRepository->findOpenRound();
        if ($round) {
            return $round;
        }

        // Defensive fallback: a round is seeded by the migration and a new one is always opened on close,
        // so this only matters if the open round was somehow removed manually (or the table is empty).
        $previousRounds = $roundRepository->findClosedRounds();
        $threshold = count($previousRounds) > 0 ? $previousRounds[0]->getThreshold() : self::DEFAULT_THRESHOLD;

        $round = new GhostRound();
        $round->setThreshold($threshold);
        $em->persist($round);
        $em->flush();

        return $round;
    }

    private function formatVoter(User $voter, CharactersRepository $charactersRepository): array
    {
        $character = $voter->getCharacterId() ? $charactersRepository->find($voter->getCharacterId()) : null;

        return [
            'id' => $voter->getId(),
            'pseudo' => $character ? $character->getPseudo() : $voter->getUsername(),
            'class' => $character?->getClass(),
        ];
    }

    private function formatCharacterSummary(Characters $character): array
    {
        return [
            'id' => $character->getId(),
            'pseudo' => $character->getPseudo(),
            'ankamaPseudo' => $character->getAnkamaPseudo(),
            'class' => $character->getClass(),
            'rank' => $character->getRank() ? [
                'id' => $character->getRank()->getId(),
                'name' => $character->getRank()->getName(),
            ] : null,
            'recruiter' => $character->getRecruiter() ? [
                'id' => $character->getRecruiter()->getId(),
                'pseudo' => $character->getRecruiter()->getPseudo(),
            ] : null,
            'mulesCount' => count($character->getMules()),
        ];
    }

    private function formatNomineeState(
        Characters $character,
        GhostRound $round,
        GhostVoteRepository $voteRepository,
        CharactersRepository $charactersRepository,
        ?User $user
    ): array {
        $votes = $voteRepository->findBy(['round' => $round, 'character' => $character]);
        $voters = array_values(array_map(
            fn($vote) => $this->formatVoter($vote->getVoter(), $charactersRepository),
            $votes
        ));
        $hasVoted = $user && in_array($user->getId(), array_column($voters, 'id'), true);

        $breakdown = $this->buildRoundBreakdown($character, $voteRepository, $charactersRepository);
        $timesReached = count(array_filter($breakdown, fn($r) => $r['thresholdReached']));

        return [
            'id' => $character->getId(),
            'voteCount' => count($voters),
            'voters' => $voters,
            'hasVoted' => $hasVoted,
            'timesThresholdReached' => $timesReached,
            'eligibleExclusion' => $timesReached >= 2,
        ];
    }

    /**
     * Per-round vote breakdown for a character, closed rounds only, most recent first.
     */
    private function buildRoundBreakdown(
        Characters $character,
        GhostVoteRepository $voteRepository,
        CharactersRepository $charactersRepository
    ): array {
        $votes = $voteRepository->findClosedVotesByCharacter($character);

        $byRound = [];
        foreach ($votes as $vote) {
            $round = $vote->getRound();
            $roundId = $round->getId();
            $byRound[$roundId] ??= ['round' => $round, 'votes' => []];
            $byRound[$roundId]['votes'][] = $vote;
        }

        $breakdown = [];
        foreach ($byRound as $entry) {
            $round = $entry['round'];
            $voteCount = count($entry['votes']);
            $breakdown[] = [
                'roundId' => $round->getId(),
                'openedAt' => $round->getOpenedAt()->format('Y-m-d'),
                'closedAt' => $round->getClosedAt()?->format('Y-m-d'),
                'threshold' => $round->getThreshold(),
                'voteCount' => $voteCount,
                'thresholdReached' => $voteCount >= $round->getThreshold(),
                'voters' => array_values(array_map(
                    fn($vote) => $this->formatVoter($vote->getVoter(), $charactersRepository),
                    $entry['votes']
                )),
            ];
        }

        usort($breakdown, fn($a, $b) => strcmp($b['closedAt'] ?? '', $a['closedAt'] ?? ''));

        return $breakdown;
    }
}
