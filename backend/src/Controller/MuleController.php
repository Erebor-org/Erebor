<?php

namespace App\Controller;

use App\Entity\Mule;
use App\Entity\Characters;
use App\Repository\MuleRepository;
use App\Repository\CharactersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\NotificationService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MuleController extends AbstractController
{

    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    #[Route('/mules', name: 'mules_list', methods: ['GET'])]
    public function getMules(MuleRepository $repository): JsonResponse
    {
        $mules = $repository->findAll();

        $formattedMules = array_map(function ($mule) {
            $mainCharacter = $mule->getMainCharacter();
            return [
                'id' => $mule->getId(),
                'mainCharacter' => [
                    'id' => $mainCharacter->getId(),
                    'pseudo' => $mainCharacter->getPseudo(),
                    'ankamaPseudo' => $mainCharacter->getAnkamaPseudo(),
                    'class' => $mainCharacter->getClass(),
                    'isArchived' => $mainCharacter->isArchived(),
                ],
                'pseudo' => $mule->getPseudo(),
                'ankamaPseudo' => $mule->getAnkamaPseudo(),
                'class' => $mule->getClass(),
                'isArchived' => $mule->isArchived(),
            ];
        }, $mules);

        return $this->json($formattedMules);
    }

    #[Route('/mules/{id}', name: 'mules_show', methods: ['GET'])]
    public function show(MuleRepository $repository, int $id): JsonResponse
    {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        return $this->json([
            'id' => $mule->getId(),
            'mainCharacter' => [
                'id' => $mule->getMainCharacter()->getId(),
                'pseudo' => $mule->getMainCharacter()->getPseudo(),
                'ankamaPseudo' => $mule->getMainCharacter()->getAnkamaPseudo(),
                'class' => $mule->getMainCharacter()->getClass(),
                'isArchived' => $mule->getMainCharacter()->isArchived(),
            ],
            'pseudo' => $mule->getPseudo(),
            'ankamaPseudo' => $mule->getAnkamaPseudo(),
            'class' => $mule->getClass(),
            'isArchived' => $mule->isArchived(),
        ]);
    }

    #[Route('/mules', name: 'mule_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CharactersRepository $charactersRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validate input
        if (!isset($data['pseudo'], $data['ankamaPseudo'], $data['class'], $data['characterId'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        // Find the main character
        $mainCharacter = $charactersRepository->find($data['characterId']);
        if (!$mainCharacter) {
            return $this->json(['error' => 'Main character not found'], 404);
        }

        // Create and persist the Mule
        $mule = new Mule();
        $mule->setPseudo($data['pseudo'])
            ->setAnkamaPseudo($data['ankamaPseudo'])
            ->setClass($data['class'])
            ->setIsArchived($data['isArchived'] ?? false)
            ->setMainCharacter($mainCharacter);

        $em->persist($mule);
        $em->flush();

        try {
            $this->notificationService->notify('mule_import', $mule);
        } catch (\Exception $e) {
            // Log the exception
            // You can also log it using a logger service if available
            error_log('Error notifying mule import: ' . $e->getMessage());
        }

        return $this->json(['message' => 'Mule created successfully'], 201);
    }

    #[Route('/mules/{id}', name: 'mules_update', methods: ['PUT'])]
    public function update(
        Request $request,
        MuleRepository $repository,
        CharactersRepository $charactersRepository,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['mainCharacterId'])) {
            $mainCharacter = $charactersRepository->find($data['mainCharacterId']);
            if (!$mainCharacter) {
                return $this->json(['error' => 'Main character not found'], 404);
            }
            $mule->setMainCharacter($mainCharacter);
        }

        $mule->setPseudo($data['pseudo'] ?? $mule->getPseudo())
             ->setAnkamaPseudo($data['ankamaPseudo'] ?? $mule->getAnkamaPseudo())
             ->setClass($data['class'] ?? $mule->getClass());

        $em->flush();

        return $this->json(['message' => 'Mule updated successfully']);
    }

    #[Route('/mules/{id}', name: 'mules_delete', methods: ['DELETE'])]
    public function delete(MuleRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        $em->remove($mule);
        $em->flush();

        return $this->json(['message' => 'Mule deleted successfully']);
    }

    #[Route('/mule/archive/{id}', name: 'mule_archive', methods: ['PUT'])]
    public function archive(MuleRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        $mule->setIsArchived(true);
        $em->flush();

        return $this->json(['message' => 'Mule archived successfully']);
    }

    #[Route('/mules/{id}/update-class', name: 'mule_update_class', methods: ['PUT'])]
    public function updateClass(
        Request $request,
        MuleRepository $repository,
        EntityManagerInterface $em,
        int $id
    ): JsonResponse {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['class'])) {
            return $this->json(['error' => 'Class is required'], 400);
        }

        $mule->setClass($data['class']);
        $em->flush();

        return $this->json(['message' => 'Mule class updated successfully']);
    }

    #[Route('/mules/{id}/update-pseudo', name: 'update_mule_pseudo', methods: ['PUT'])]
    public function updatePseudo(Request $request, MuleRepository $repository, EntityManagerInterface $em, int $id): JsonResponse
    {
        $mule = $repository->find($id);

        if (!$mule) {
            return $this->json(['error' => 'Mule not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['pseudo'])) {
            return $this->json(['error' => 'Missing class field'], 400);
        }

        $mule->setPseudo($data['pseudo']);
        $em->flush();

        return $this->json(['message' => 'Pseudo updated successfully'], 200);
    }
}
