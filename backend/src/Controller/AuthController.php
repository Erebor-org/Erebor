<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController
{
    private $userRepository;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route(\"/api/login\", name=\"api_login\", methods={\"POST\"})
     */
    public function login(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->userRepository->findOneBy(['username' => $data['username']]);

        if ($user && $this->passwordEncoder->isPasswordValid($user, $data['password'])) {
            $session->set('user_id', $user->getId());
            return new JsonResponse(['success' => true, 'message' => 'Login successful']);
        }

        return new JsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
    }
}

