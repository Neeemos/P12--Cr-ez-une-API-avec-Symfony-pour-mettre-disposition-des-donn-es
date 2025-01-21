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

class UserController extends AbstractController
{
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
