<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // <-- Importer AbstractController
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;

class LoginController extends AbstractController  // <-- Étendre AbstractController
{
    private $security;
    private $jwtManager;

    // Injection de la dépendance Security et du service JWT
    public function __construct(Security $security, JWTTokenManagerInterface $jwtManager)
    {
        $this->security = $security;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/api/login', name: 'app_login', methods: ['POST'])]
    public function index(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Invalid login credentials.'], 401);
        }

        // Générer le JWT token
        $token = $this->jwtManager->create($user);

        // Retourner le JWT dans la réponse
        return new JsonResponse([
            'message' => 'Login successful!',
            'user' => [
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ],
            'token' => $token  // Ajout du token JWT dans la réponse
        ]);
    }

    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Créer une nouvelle instance de User
        $body = $request->getContent();
        $user = new User();
        $user->setUsername(json_decode($body)->username);
        $user->setPassword($passwordHasher->hashPassword($user, json_decode($body)->password));
        $user->setCity(json_decode($body)->city);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur créé avec succès.'], 201);
    }

}
