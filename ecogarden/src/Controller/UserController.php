<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    private $security;
    private $jwtManager;

    // Injection de la dépendance Security et du service JWT
    public function __construct(Security $security, JWTTokenManagerInterface $jwtManager)
    {
        $this->security = $security;
        $this->jwtManager = $jwtManager;
    }

/** 
 * Login un utilisateur
 *  
 * @return JsonResponse
 * 
 * @throws \InvalidArgumentException
 */
    #[Route('/auth', name: 'app_login', methods: ['POST'])]
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
/** 
 * register un utilisateur
 * @param Request $request
 * @return JsonResponse
 * 
 * @throws \InvalidArgumentException
 */
    #[Route('/user', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        // Créer une nouvelle instance de User
        $body = $request->getContent();
        $user = new User();
        $user->setUsername(json_decode($body)->username);
        $user->setPassword($passwordHasher->hashPassword($user, json_decode($body)->password));
        $user->setCity(json_decode($body)->city);
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur créé avec succès.'], 201);
    }

/** 
 * Supprimer un utilisateur
 * @param User $user
 * @return JsonResponse
 * 
 * @throws \InvalidArgumentException
 */
    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(
        User $user,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authChecker
    ): JsonResponse {

        // Si l'utilisateur à supprimer est l'utilisateur connecté, retourner une erreur
        if ($user === $this->getUser()) {
            return new JsonResponse(['error' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        // Supprimer l'utilisateur 
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès.'], 200);
    }
    #[Route('/user/{id}', name: 'update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')] // Vérifie que l'utilisateur a le rôle ROLE_ADMIN
    public function updateUser(
        User $user,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Décoder les données de la requête
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // Mise à jour des champs
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        if (isset($data['city'])) {
            $user->setCity($data['city']);
        }
        if (isset($data['roles']) && is_array($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        // Persiste les modifications
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès.'], 200);
    }
}
