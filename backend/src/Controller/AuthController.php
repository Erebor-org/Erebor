<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\CharactersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private CharactersRepository $charactersRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        CharactersRepository $charactersRepository
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->charactersRepository = $charactersRepository;
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, JWTTokenManagerInterface $jwtManager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Support both 'username' and 'pseudo' for backward compatibility
        $username = $data['username'] ?? $data['pseudo'] ?? null;

        if (!$username || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Missing required fields (username and password)'], 400);
        }

        // Validate username length
        if (strlen($username) < 3) {
            return new JsonResponse(['error' => 'Username must be at least 3 characters long'], 400);
        }

        // Check if username already exists
        $existingUser = $userRepository->findOneBy(['username' => $username]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Username already exists'], 409);
        }

        // Validate password length
        if (strlen($data['password']) < 6) {
            return new JsonResponse(['error' => 'Password must be at least 6 characters long'], 400);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        // Set characterId if provided
        if (isset($data['characterId']) && $data['characterId'] !== null) {
            // Validate that the character exists and is not archived
            $character = $this->charactersRepository->find($data['characterId']);
            if (!$character) {
                return new JsonResponse(['error' => 'Character not found'], 404);
            }
            if ($character->isArchived()) {
                return new JsonResponse(['error' => 'Cannot link account to archived character'], 400);
            }
            
            // Check if character is already linked to another user
            $existingUserWithCharacter = $userRepository->findOneBy(['characterId' => $data['characterId']]);
            if ($existingUserWithCharacter) {
                return new JsonResponse(['error' => 'Character is already linked to another account'], 409);
            }
            
            $user->setCharacterId($data['characterId']);
        }

        // Set default rank if not provided
        $user->setRank($data['rank'] ?? 'user');
        
        // Set default role (ROLE_USER) if not provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Generate JWT token for the new user
        $token = $jwtManager->create($user);

        // Get character if linked
        $character = null;
        if ($user->getCharacterId()) {
            $character = $this->charactersRepository->find($user->getCharacterId());
        }

        // Get user data
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

        return new JsonResponse([
            'message' => 'User created successfully',
            'token' => $token,
            'user' => $userData
        ], 201);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Missing username or password'], 400);
        }

        // Find user by username
        $user = $userRepository->findOneBy(['username' => $data['username']]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Clear any forced disconnect flag on login
        $user->setForceDisconnectAt(null);
        $this->entityManager->flush();

        $token = $jwtManager->create($user);

        // Get character if linked
        $character = null;
        if ($user->getCharacterId()) {
            $character = $this->charactersRepository->find($user->getCharacterId());
        }

        // Get user data
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
        
        // Return both token and user data
        return new JsonResponse([
            'token' => $token,
            'user' => $userData
        ]);
    }
}

?>