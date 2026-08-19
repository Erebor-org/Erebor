<?php

namespace App\Controller;

use App\Entity\Blacklist;
use App\Entity\BlacklistCharacter;
use App\Repository\BlacklistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\NotificationService;

class BlacklistController extends AbstractController
{

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    private function formatBlacklist(Blacklist $blacklist): array
    {
        return [
            'id' => $blacklist->getId(),
            'pseudo' => $blacklist->getPseudo(),
            'ankamaPseudo' => $blacklist->getAnkamaPseudo(),
            'reason' => $blacklist->getReason(),
            'associatedCharacters' => array_map(
                fn (BlacklistCharacter $c) => [
                    'id' => $c->getId(),
                    'pseudo' => $c->getPseudo(),
                    'ankamaPseudo' => $c->getAnkamaPseudo(),
                ],
                $blacklist->getAssociatedCharacters()->toArray()
            ),
        ];
    }

    #[Route('/blacklist', name: 'blacklist_get', methods: ['GET'])]
    public function getBlacklist(BlacklistRepository $blacklistRepository): JsonResponse
    {
        $blacklists = $blacklistRepository->findAll();

        return $this->json(array_map(fn (Blacklist $b) => $this->formatBlacklist($b), $blacklists));
    }

    #[Route('/blacklist', name: 'blacklist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['pseudo']) || empty($data['ankamaPseudo']) || empty($data['reason'])) {
            return $this->json(['message' => 'pseudo, ankamaPseudo et reason sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $blacklist = new Blacklist();
        $blacklist->setPseudo($data['pseudo']);
        $blacklist->setAnkamaPseudo($data['ankamaPseudo']);
        $blacklist->setReason($data['reason']);

        foreach ($data['associatedCharacters'] ?? [] as $associated) {
            if (empty($associated['pseudo'])) {
                continue;
            }
            $character = new BlacklistCharacter();
            $character->setPseudo($associated['pseudo']);
            $character->setAnkamaPseudo($associated['ankamaPseudo'] ?? null);
            $blacklist->addAssociatedCharacter($character);
        }

        $entityManager->persist($blacklist);
        $entityManager->flush();
        $this->notificationService->notify('blacklist_added', $blacklist);

        return $this->json($this->formatBlacklist($blacklist), Response::HTTP_CREATED);
    }

    #[Route('/blacklist/{id}', name: 'blacklist_update', methods: ['PUT'])]
    public function update(int $id, Request $request, BlacklistRepository $blacklistRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $blacklist = $blacklistRepository->find($id);

        if (!$blacklist) {
            return $this->json(['message' => 'Blacklist entry not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['pseudo'])) {
            $blacklist->setPseudo($data['pseudo']);
        }
        if (!empty($data['ankamaPseudo'])) {
            $blacklist->setAnkamaPseudo($data['ankamaPseudo']);
        }
        if (!empty($data['reason'])) {
            $blacklist->setReason($data['reason']);
        }

        $entityManager->flush();

        return $this->json($this->formatBlacklist($blacklist));
    }

    #[Route('/blacklist/{id}', name: 'blacklist_delete', methods: ['DELETE'])]
    public function delete(int $id, BlacklistRepository $blacklistRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $blacklist = $blacklistRepository->find($id);

        if (!$blacklist) {
            return $this->json(['message' => 'Blacklist entry not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($blacklist);
        $entityManager->flush();

        return $this->json(['message' => 'Blacklist entry deleted']);
    }

    #[Route('/blacklist/{id<\d+>}', name: 'blacklist_show', methods: ['GET'])]
    public function show(int $id, BlacklistRepository $blacklistRepository): JsonResponse
    {
        $blacklist = $blacklistRepository->find($id);

        if (!$blacklist) {
            return $this->json(['message' => 'Blacklist entry not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->formatBlacklist($blacklist));
    }

    #[Route('/blacklist/{id}/characters', name: 'blacklist_character_create', methods: ['POST'])]
    public function addAssociatedCharacter(int $id, Request $request, BlacklistRepository $blacklistRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $blacklist = $blacklistRepository->find($id);

        if (!$blacklist) {
            return $this->json(['message' => 'Blacklist entry not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['pseudo'])) {
            return $this->json(['message' => 'pseudo est requis'], Response::HTTP_BAD_REQUEST);
        }

        $character = new BlacklistCharacter();
        $character->setPseudo($data['pseudo']);
        $character->setAnkamaPseudo($data['ankamaPseudo'] ?? null);
        $blacklist->addAssociatedCharacter($character);

        $entityManager->persist($character);
        $entityManager->flush();

        return $this->json($this->formatBlacklist($blacklist), Response::HTTP_CREATED);
    }

    #[Route('/blacklist/{id}/characters/{characterId}', name: 'blacklist_character_delete', methods: ['DELETE'])]
    public function removeAssociatedCharacter(int $id, int $characterId, BlacklistRepository $blacklistRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $blacklist = $blacklistRepository->find($id);

        if (!$blacklist) {
            return $this->json(['message' => 'Blacklist entry not found'], Response::HTTP_NOT_FOUND);
        }

        $character = null;
        foreach ($blacklist->getAssociatedCharacters() as $candidate) {
            if ($candidate->getId() === $characterId) {
                $character = $candidate;
                break;
            }
        }

        if (!$character) {
            return $this->json(['message' => 'Associated character not found'], Response::HTTP_NOT_FOUND);
        }

        $blacklist->removeAssociatedCharacter($character);
        $entityManager->remove($character);
        $entityManager->flush();

        return $this->json($this->formatBlacklist($blacklist));
    }
}
