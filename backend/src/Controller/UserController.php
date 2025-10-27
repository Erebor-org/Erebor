<?php
// src/Controller/ApiController.php
namespace App\Controller;

use App\Entity\Characters;
use App\Repository\CharactersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user', methods: ['GET'])]
    public function user(): JsonResponse
    {
        return new JsonResponse(['message' => 'hello user']);
    }

    #[Route('/user/character', name: 'update_user_character', methods: ['PUT'])]
    public function updateCharacter(
        Request $request,
        EntityManagerInterface $em,
        CharactersRepository $charactersRepository,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['characterId'])) {
            return new JsonResponse(['error' => 'Missing characterId'], 400);
        }

        // If characterId is null, unset the association
        if ($data['characterId'] === null) {
            $user->setCharacterId(null);
            $em->flush();
            
            return new JsonResponse([
                'message' => 'Character association removed',
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'roles' => $user->getRoles(),
                    'characterId' => null,
                    'character' => null
                ]
            ]);
        }

        // Validate that the character exists
        $character = $charactersRepository->find($data['characterId']);
        if (!$character) {
            return new JsonResponse(['error' => 'Character not found'], 404);
        }

        $user->setCharacterId($data['characterId']);
        $em->flush();

        return new JsonResponse([
            'message' => 'Character association updated',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'characterId' => $user->getCharacterId(),
                'character' => [
                    'id' => $character->getId(),
                    'pseudo' => $character->getPseudo(),
                    'ankamaPseudo' => $character->getAnkamaPseudo(),
                    'class' => $character->getClass(),
                ]
            ]
        ]);
    }

    #[Route('/user/profile', name: 'get_user_profile', methods: ['GET'])]
    public function getProfile(
        CharactersRepository $charactersRepository,
        #[CurrentUser] \App\Entity\User $user
    ): JsonResponse {
        $character = null;
        
        if ($user->getCharacterId()) {
            $character = $charactersRepository->find($user->getCharacterId());
        }

        $userData = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'characterId' => $user->getCharacterId(),
            'character' => $character ? [
                'id' => $character->getId(),
                'pseudo' => $character->getPseudo(),
                'ankamaPseudo' => $character->getAnkamaPseudo(),
                'class' => $character->getClass(),
            ] : null
        ];

        return new JsonResponse($userData);
    }
}