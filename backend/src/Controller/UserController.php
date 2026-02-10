<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/api/me', methods: ['GET'])]
    public function me(UserPresenter $presenter): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($presenter->presentMe($user));
    }

    #[Route('/api/user/{username}', name: 'app_user', methods: ['GET'])]
    public function getUserProfile(string $username, EntityManagerInterface $em, UserPresenter $presenter): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($presenter->presentPublic($user));
    }
}
