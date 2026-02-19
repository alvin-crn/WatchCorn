<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_')]
final class UserController extends AbstractController
{
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse([
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'restricted' => $user->isRestricted(),
            'profilePic' => $user->getProfilePic(),
        ]);
    }

    #[Route('/user/{username}', name: 'api_user', methods: ['GET'])]
    public function getUserProfile(string $username, EntityManagerInterface $em, UserPresenter $presenter): JsonResponse
    #[Route('/user/{username}', name: 'user_profile', methods: ['GET'])]
    public function getUserProfile(string $username, EntityManagerInterface $em, UserService $userService): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($presenter->presentPublic($user));
        return new JsonResponse($userService->presentPublic($user));
    }
    }
}
