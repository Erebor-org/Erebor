<?php

namespace App\Controller;

use App\Entity\Warning;
use App\Entity\Characters;
use App\Repository\WarningRepository;
use App\Repository\CharactersRepository;
use App\Repository\UserRepository;
use App\Repository\RanksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\NotificationService;

class WarningController extends AbstractController
{


    private NotificationService $notificationService;
    private WarningRepository $warningRepository;

    public function __construct(NotificationService $notificationService, WarningRepository $warningRepository)
    {
        $this->notificationService = $notificationService;
        $this->warningRepository = $warningRepository;
    }

    #[Route('/warnings', name: 'warnings_get_all', methods: ['GET'])]
    public function getAllWarnings(
        WarningRepository $warningRepository
    ): JsonResponse {
        $warnings = $warningRepository->findAll();
        
        // Format the response
        $formattedWarnings = array_map(function ($warning) {
            return [
                'id' => $warning->getId(),
                'description' => $warning->getDescription(),
                'createdAt' => $warning->getCreatedAt()->format('Y-m-d H:i:s'),
                'character' => [
                    'id' => $warning->getCharacter()->getId(),
                    'pseudo' => $warning->getCharacter()->getPseudo(),
                    'class' => $warning->getCharacter()->getClass(),
                ],
                'authorCharacter' => $warning->getAuthorCharacter() ? [
                    'id' => $warning->getAuthorCharacter()->getId(),
                    'pseudo' => $warning->getAuthorCharacter()->getPseudo(),
                    'class' => $warning->getAuthorCharacter()->getClass(),
                ] : null,
            ];
        }, $warnings);
        
        return $this->json($formattedWarnings);
    }
    
    #[Route('/characters/lead', name: 'characters_with_lead', methods: ['GET'])]
    public function getCharactersWithLead(
        CharactersRepository $charactersRepository,
        RanksRepository $ranksRepository
    ): JsonResponse {
        $ranks = $ranksRepository->findBy(['lead' => true]);
        $characters = [];
        
        foreach ($ranks as $rank) {
            $charactersWithRank = $charactersRepository->createQueryBuilder('c')
                ->where('c.rank = :rank')
                ->andWhere('c.isArchived = :isArchived')
                ->setParameter('rank', $rank)
                ->setParameter('isArchived', false)
                ->orderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();
            $characters = array_merge($characters, $charactersWithRank);
        }
        
        // Trier les personnages par requiredDays du rang (ordre décroissant)
        usort($characters, function($a, $b) {
            $rankA = $a->getRank();
            $rankB = $b->getRank();
            
            if ($rankA->getRequiredDays() === $rankB->getRequiredDays()) {
                return $a->getId() - $b->getId();
            }
            
            return $rankB->getRequiredDays() - $rankA->getRequiredDays();
        });
        
        $formattedCharacters = array_map(function ($character) {
            return [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'class' => $character->getClass(),
                'rank' => [
                    'id' => $character->getRank()->getId(),
                    'name' => $character->getRank()->getName(),
                ]
            ];
        }, $characters);
        
        return $this->json($formattedCharacters);
    }
    #[Route('/warnings/character/{id}', name: 'warnings_by_character', methods: ['GET'])]
    public function getWarningsByCharacter(
        int $id,
        CharactersRepository $charactersRepository,
        WarningRepository $warningRepository
    ): JsonResponse {
        $character = $charactersRepository->find($id);
        
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
        
        $warnings = $warningRepository->findByCharacter($character);
        
        // Format the response
        $formattedWarnings = array_map(function ($warning) {
            return [
                'id' => $warning->getId(),
                'description' => $warning->getDescription(),
                'createdAt' => $warning->getCreatedAt()->format('Y-m-d H:i:s'),
                'character' => [
                    'id' => $warning->getCharacter()->getId(),
                    'pseudo' => $warning->getCharacter()->getPseudo(),
                ],
                'authorCharacter' => $warning->getAuthorCharacter() ? [
                    'id' => $warning->getAuthorCharacter()->getId(),
                    'pseudo' => $warning->getAuthorCharacter()->getPseudo(),
                    'class' => $warning->getAuthorCharacter()->getClass(),
                ] : null,
            ];
        }, $warnings);
        
        return $this->json($formattedWarnings);
    }

    #[Route('/warnings', name: 'warnings_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CharactersRepository $charactersRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        #[CurrentUser] ?\App\Entity\User $user
    ): JsonResponse {
        // Only OWNERS can create warnings
        if (!$user || !in_array('ROLE_OWNERS', $user->getRoles())) {
            return $this->json(['error' => 'Access denied. Only Owners can create warnings.'], 403);
        }
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        if (!isset($data['characterId']) || !isset($data['description']) || !isset($data['authorCharacterId'])) {
            return $this->json(['error' => 'Character ID, author character ID, and description are required'], 400);
        }
        
        // Find character
        $character = $charactersRepository->find($data['characterId']);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
        
        // Find author character
        $authorCharacter = $charactersRepository->find($data['authorCharacterId']);
        if (!$authorCharacter) {
            return $this->json(['error' => 'Author character not found'], 404);
        }
        
        // Validate author character has lead rank
        if (!$authorCharacter->getRank()->getLead()) {
            return $this->json(['error' => 'Author character must have lead rank'], 400);
        }
        
        // Create warning
        $warning = new Warning();
        $warning->setCharacter($character)
                ->setDescription($data['description'])
                ->setAuthorCharacter($authorCharacter);
        
        // Validate warning entity
        $errors = $validator->validate($warning);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        
        // Save to database
        $em->persist($warning);
        $em->flush();
        $totalWarnings = $this->warningRepository->count(['character' => $warning->getCharacter()]);
        $this->notificationService->notify('warning_added', [
            'warning' => $warning,
            'count' => $totalWarnings,
        ]);
        
        // Return formatted response
        return $this->json([
            'id' => $warning->getId(),
            'description' => $warning->getDescription(),
            'createdAt' => $warning->getCreatedAt()->format('Y-m-d H:i:s'),
            'character' => [
                'id' => $warning->getCharacter()->getId(),
                'pseudo' => $warning->getCharacter()->getPseudo(),
            ],
            'authorCharacter' => $warning->getAuthorCharacter() ? [
                'id' => $warning->getAuthorCharacter()->getId(),
                'pseudo' => $warning->getAuthorCharacter()->getPseudo(),
                'class' => $warning->getAuthorCharacter()->getClass(),
            ] : null,
        ], 201);
    }

    #[Route('/warnings/{id}', name: 'warnings_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        WarningRepository $warningRepository,
        ValidatorInterface $validator,
        #[CurrentUser] ?\App\Entity\User $user
    ): JsonResponse {
        // Only OWNERS can update warnings
        if (!$user || !in_array('ROLE_OWNERS', $user->getRoles())) {
            return $this->json(['error' => 'Access denied. Only Owners can update warnings.'], 403);
        }
        $warning = $warningRepository->find($id);
        
        if (!$warning) {
            return $this->json(['error' => 'Warning not found'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        // Update description if provided
        if (isset($data['description'])) {
            $warning->setDescription($data['description']);
        }
        
        // Validate warning entity
        $errors = $validator->validate($warning);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        
        // Save to database
        $em->flush();
        
        // Return formatted response
        return $this->json([
            'id' => $warning->getId(),
            'description' => $warning->getDescription(),
            'createdAt' => $warning->getCreatedAt()->format('Y-m-d H:i:s'),
            'character' => [
                'id' => $warning->getCharacter()->getId(),
                'pseudo' => $warning->getCharacter()->getPseudo(),
            ],
            'authorCharacter' => $warning->getAuthorCharacter() ? [
                'id' => $warning->getAuthorCharacter()->getId(),
                'pseudo' => $warning->getAuthorCharacter()->getPseudo(),
                'class' => $warning->getAuthorCharacter()->getClass(),
            ] : null,
        ]);
    }

    #[Route('/warnings/{id}', name: 'warnings_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        WarningRepository $warningRepository,
        EntityManagerInterface $em,
        #[CurrentUser] ?\App\Entity\User $user
    ): JsonResponse {
        // Only OWNERS can delete warnings
        if (!$user || !in_array('ROLE_OWNERS', $user->getRoles())) {
            return $this->json(['error' => 'Access denied. Only Owners can delete warnings.'], 403);
        }
        $warning = $warningRepository->find($id);
        
        if (!$warning) {
            return $this->json(['error' => 'Warning not found'], 404);
        }
        
        $em->remove($warning);
        $em->flush();
        
        return $this->json(['message' => 'Warning deleted successfully']);
    }
}
