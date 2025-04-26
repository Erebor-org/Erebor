<?php

namespace App\Controller;

use App\Entity\Warning;
use App\Entity\Characters;
use App\Repository\WarningRepository;
use App\Repository\CharactersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
// use Symfony\Component\Security\Core\Security;

class WarningController extends AbstractController
{
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
                'author' => $warning->getAuthor() ? [
                    'id' => $warning->getAuthor()->getId(),
                    'username' => $warning->getAuthor()->getUsername(),
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
        // Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        // Validate required fields
        if (!isset($data['characterId']) || !isset($data['description'])) {
            return $this->json(['error' => 'Character ID and description are required'], 400);
        }
        
        // Find character
        $character = $charactersRepository->find($data['characterId']);
        if (!$character) {
            return $this->json(['error' => 'Character not found'], 404);
        }
        
        // Create warning
        $warning = new Warning();
        $warning->setCharacter($character)
                ->setDescription($data['description']);
        
        // Set author to current user if authenticated
        // $currentUser = $security->getUser();
        // if ($currentUser) {
        //     $warning->setAuthor($currentUser);
        // }
        // Override with explicit author if provided and user has admin rights
        // elseif (isset($data['authorId']) && $this->isGranted('ROLE_ADMIN')) {
        //     $author = $userRepository->find($data['authorId']);
        //     if ($author) {
        //         $warning->setAuthor($author);
        //     }
        // }
        
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
        
        // Return formatted response
        return $this->json([
            'id' => $warning->getId(),
            'description' => $warning->getDescription(),
            'createdAt' => $warning->getCreatedAt()->format('Y-m-d H:i:s'),
            'character' => [
                'id' => $warning->getCharacter()->getId(),
                'pseudo' => $warning->getCharacter()->getPseudo(),
            ],
            'author' => $warning->getAuthor() ? [
                'id' => $warning->getAuthor()->getId(),
                'username' => $warning->getAuthor()->getUsername(),
            ] : null,
        ], 201);
    }

    #[Route('/warnings/{id}', name: 'warnings_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        WarningRepository $warningRepository,
        ValidatorInterface $validator
    ): JsonResponse {
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
            'author' => $warning->getAuthor() ? [
                'id' => $warning->getAuthor()->getId(),
                'username' => $warning->getAuthor()->getUsername(),
            ] : null,
        ]);
    }

    #[Route('/warnings/{id}', name: 'warnings_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        WarningRepository $warningRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $warning = $warningRepository->find($id);
        
        if (!$warning) {
            return $this->json(['error' => 'Warning not found'], 404);
        }
        
        $em->remove($warning);
        $em->flush();
        
        return $this->json(['message' => 'Warning deleted successfully']);
    }
}
