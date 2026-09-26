<?php

namespace App\Controller;

use App\Entity\Characters;
use App\Entity\Ranks;
use App\Repository\CharactersRepository;
use App\Repository\RanksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Special ("function") ranks: every rank without required seniority (leads, enroleur, troubadour...).
 * Give, change or remove such a rank on a character. Only touches the character's rank,
 * never the user's security roles.
 * OWNERS only (also enforced by access_control on ^/admin/special-ranks).
 */
#[IsGranted('ROLE_OWNERS')]
class SpecialRankController extends AbstractController
{
    #[Route('/admin/special-ranks', name: 'special_ranks_overview', methods: ['GET'])]
    public function overview(CharactersRepository $charactersRepository, RanksRepository $ranksRepository): JsonResponse
    {
        $characters = $charactersRepository->createQueryBuilder('c')
            ->join('c.rank', 'r')
            ->addSelect('r')
            ->where('c.isArchived = false')
            ->orderBy('c.pseudo', 'ASC')
            ->getQuery()
            ->getResult();

        $members = [];
        $candidates = [];
        foreach ($characters as $character) {
            if ($this->isSpecial($character->getRank())) {
                $members[] = $this->formatCharacter($character, $ranksRepository);
            } else {
                $candidates[] = $this->formatCharacter($character, $ranksRepository);
            }
        }

        $specialRanks = [];
        $classicRanks = [];
        foreach ($ranksRepository->findAllOrderedByRequiredDays() as $rank) {
            if ($this->isSpecial($rank)) {
                $specialRanks[] = $this->formatRank($rank);
            } else {
                $classicRanks[] = $this->formatRank($rank);
            }
        }

        return $this->json([
            'members' => $members,
            'candidates' => $candidates,
            'specialRanks' => $specialRanks,
            'classicRanks' => $classicRanks,
        ]);
    }

    // Give a special rank to a character, or switch a character between special ranks
    #[Route('/admin/special-ranks/{id<\d+>}', name: 'special_ranks_set', methods: ['PUT'])]
    public function setSpecialRank(
        int $id,
        Request $request,
        CharactersRepository $charactersRepository,
        RanksRepository $ranksRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $character = $charactersRepository->find($id);
        if (!$character || $character->isArchived()) {
            return $this->json(['error' => 'Personnage introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $rank = isset($data['rankId']) ? $ranksRepository->find($data['rankId']) : null;
        if (!$rank || !$this->isSpecial($rank)) {
            return $this->json(['error' => "Ce rang n'est pas un rang de fonction"], 400);
        }

        $character->setRank($rank);
        $em->flush();

        return $this->json($this->formatCharacter($character, $ranksRepository));
    }

    // Remove the special rank: the character goes back to a classic (seniority-based) rank
    #[Route('/admin/special-ranks/{id<\d+>}/remove', name: 'special_ranks_remove', methods: ['POST'])]
    public function removeSpecialRank(
        int $id,
        Request $request,
        CharactersRepository $charactersRepository,
        RanksRepository $ranksRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $character = $charactersRepository->find($id);
        if (!$character || !$this->isSpecial($character->getRank())) {
            return $this->json(['error' => "Ce personnage n'a pas de rang de fonction"], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $rank = isset($data['rankId'])
            ? $ranksRepository->find($data['rankId'])
            : $ranksRepository->findClassicRankForDays($this->seniorityDays($character));
        if (!$rank || $this->isSpecial($rank)) {
            return $this->json(['error' => "Ce rang n'est pas un rang d'ancienneté"], 400);
        }

        $character->setRank($rank);
        $em->flush();

        return $this->json($this->formatCharacter($character, $ranksRepository));
    }

    // A special rank is any rank not earned through seniority (includes lead ranks)
    private function isSpecial(Ranks $rank): bool
    {
        return $rank->getLead() || $rank->getRequiredDays() === null;
    }

    private function seniorityDays(Characters $character): int
    {
        // Same rule as character creation: at least 1 day
        return max(1, $character->getRecruitedAt()->diff(new \DateTime())->days);
    }

    private function formatCharacter(Characters $character, RanksRepository $ranksRepository): array
    {
        $days = $this->seniorityDays($character);
        $seniorityRank = $ranksRepository->findClassicRankForDays($days);

        return [
            'id' => $character->getId(),
            'pseudo' => $character->getPseudo(),
            'ankamaPseudo' => $character->getAnkamaPseudo(),
            'class' => $character->getClass(),
            'recruitedAt' => $character->getRecruitedAt()->format('Y-m-d'),
            'seniorityDays' => $days,
            'rank' => $this->formatRank($character->getRank()),
            // Classic rank matching the character's seniority, suggested when removing the special rank
            'seniorityRankId' => $seniorityRank?->getId(),
        ];
    }

    private function formatRank(Ranks $rank): array
    {
        return [
            'id' => $rank->getId(),
            'name' => $rank->getName(),
            'description' => $rank->getDescription(),
            'requiredDays' => $rank->getRequiredDays(),
            'lead' => $rank->getLead(),
            'recruiter' => $rank->getRecruiter(),
        ];
    }
}
