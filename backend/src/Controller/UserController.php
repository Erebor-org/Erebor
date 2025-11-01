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
use App\Repository\UserRepository;

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
                    'recruitedAt' => $character->getRecruitedAt()->format('Y-m-d'),
                    'rank' => [
                        'id' => $character->getRank()->getId(),
                        'name' => $character->getRank()->getName(),
                        'description' => $character->getRank()->getDescription(),
                        'requiredDays' => $character->getRank()->getRequiredDays(),
                        'lead' => $character->getRank()->getLead(),
                        'recruiter' => $character->getRank()->getRecruiter(),
                        'needUpdate' => $character->getRank()->getNeedUpdate(),
                    ]
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
            // Fetch character with mules
            $character = $charactersRepository->createQueryBuilder('c')
                ->leftJoin('c.mules', 'mules')
                ->addSelect('mules')
                ->where('c.id = :id')
                ->setParameter('id', $user->getCharacterId())
                ->getQuery()
                ->getOneOrNullResult();
        }

        // Format mules
        $muleList = [];
        if ($character) {
            foreach ($character->getMules() as $mule) {
                if (!$mule->isArchived()) {
                    $muleList[] = [
                        'id' => $mule->getId(),
                        'pseudo' => $mule->getPseudo(),
                        'ankamaPseudo' => $mule->getAnkamaPseudo(),
                        'class' => $mule->getClass(),
                        'isArchived' => $mule->isArchived()
                    ];
                }
            }
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
                'recruitedAt' => $character->getRecruitedAt()->format('Y-m-d'),
                'mules' => $muleList,
                'rank' => [
                    'id' => $character->getRank()->getId(),
                    'name' => $character->getRank()->getName(),
                    'description' => $character->getRank()->getDescription(),
                    'requiredDays' => $character->getRank()->getRequiredDays(),
                    'lead' => $character->getRank()->getLead(),
                    'recruiter' => $character->getRank()->getRecruiter(),
                    'needUpdate' => $character->getRank()->getNeedUpdate(),
                ]
            ] : null
        ];

        return new JsonResponse($userData);
    }

    // Admin endpoints
    #[Route('/admin/users', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(
        UserRepository $userRepository,
        CharactersRepository $charactersRepository
    ): JsonResponse {
        $users = $userRepository->findAll();
        
        $usersData = [];
        foreach ($users as $user) {
            $character = null;
            if ($user->getCharacterId()) {
                $character = $charactersRepository->find($user->getCharacterId());
            }
            
            $usersData[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'rank' => $user->getRank(),
                'characterId' => $user->getCharacterId(),
                'character' => $character ? [
                    'id' => $character->getId(),
                    'pseudo' => $character->getPseudo(),
                    'ankamaPseudo' => $character->getAnkamaPseudo(),
                    'class' => $character->getClass(),
                ] : null,
                'forceDisconnectAt' => $user->getForceDisconnectAt()?->format('Y-m-d H:i:s')
            ];
        }
        
        return new JsonResponse($usersData);
    }

    #[Route('/admin/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        CharactersRepository $charactersRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Update roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
            // Force disconnect when roles are changed
            $user->setForceDisconnectAt(new \DateTime());
        }

        // Update character association if provided
        if (isset($data['characterId'])) {
            $oldCharacterId = $user->getCharacterId();
            if ($data['characterId'] === null) {
                $user->setCharacterId(null);
            } else {
                $character = $charactersRepository->find($data['characterId']);
                if (!$character) {
                    return new JsonResponse(['error' => 'Character not found'], 404);
                }
                $user->setCharacterId($data['characterId']);
            }
            
            // Force disconnect if character was changed (different from current)
            if ($oldCharacterId !== $user->getCharacterId()) {
                $user->setForceDisconnectAt(new \DateTime());
            }
        }

        $em->flush();

        // Get updated character info
        $character = null;
        if ($user->getCharacterId()) {
            $character = $charactersRepository->find($user->getCharacterId());
        }

        return new JsonResponse([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'characterId' => $user->getCharacterId(),
                'character' => $character ? [
                    'id' => $character->getId(),
                    'pseudo' => $character->getPseudo(),
                    'ankamaPseudo' => $character->getAnkamaPseudo(),
                    'class' => $character->getClass(),
                    'rank' => [
                        'id' => $character->getRank()->getId(),
                        'name' => $character->getRank()->getName(),
                        'description' => $character->getRank()->getDescription(),
                        'requiredDays' => $character->getRank()->getRequiredDays(),
                        'lead' => $character->getRank()->getLead(),
                        'recruiter' => $character->getRank()->getRecruiter(),
                        'needUpdate' => $character->getRank()->getNeedUpdate(),
                    ]
                ] : null
            ]
        ]);
    }

    #[Route('/admin/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Check if trying to delete the current admin user
        $currentUser = $this->getUser();
        if ($currentUser && $currentUser->getId() === $user->getId()) {
            return new JsonResponse(['error' => 'You cannot delete your own account'], 400);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'User deleted successfully']);
    }

    #[Route('/admin/users/{id}/disconnect', name: 'force_disconnect_user', methods: ['POST'])]
    public function forceDisconnectUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Set the force disconnect timestamp
        $user->setForceDisconnectAt(new \DateTime());
        $em->flush();
        
        return new JsonResponse([
            'message' => 'User flagged for disconnect',
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'disconnectedAt' => $user->getForceDisconnectAt()?->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/user/check-disconnect', name: 'check_user_disconnect', methods: ['GET'])]
    public function checkUserDisconnect(
        #[CurrentUser] \App\Entity\User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $forceDisconnectAt = $user->getForceDisconnectAt();
        $shouldDisconnect = $forceDisconnectAt !== null;
        
        // If user should disconnect, clear the flag after detecting it
        if ($shouldDisconnect) {
            $user->setForceDisconnectAt(null);
            $em->flush();
        }
        
        return new JsonResponse([
            'shouldDisconnect' => $shouldDisconnect,
            'disconnectedAt' => $forceDisconnectAt?->format('Y-m-d H:i:s')
        ]);
    }
}