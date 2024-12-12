<?php

namespace App\Controller;

use App\Entity\Blacklist;
use App\Repository\BlacklistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlacklistController extends AbstractController
{
    #[Route('/blacklist', name: 'blacklist_get', methods: ['GET'])]
    public function getBlacklist(BlacklistRepository $blacklistRepository): JsonResponse
    {
        $blacklists = $blacklistRepository->findAll();

        return $this->json($blacklists);
    }

    #[Route('/blacklist', name: 'blacklist_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $blacklist = new Blacklist();
        $blacklist->setPseudo($data['pseudo'] ?? null);
        $blacklist->setAnkamaPseudo($data['ankamaPseudo'] ?? null);
        $blacklist->setReason($data['reason'] ?? null);

        $entityManager->persist($blacklist);
        $entityManager->flush();

        return $this->json(['message' => 'Blacklist entry created'], Response::HTTP_CREATED);
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

        return $this->json($blacklist);
    }
}