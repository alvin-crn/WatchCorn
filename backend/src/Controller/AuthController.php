<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auth', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    public function __construct(private AuthService $authService) {}

    #[Route('/refreshToken', name: 'refresh_token', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return new JsonResponse(['error' => 'Refresh token is required'], 400);
        }

        $jwt = $this->authService->refreshToken($refreshToken);

        if (!$jwt) {
            return new JsonResponse(['error' => 'Invalid refresh token'], 401);
        }

        return new JsonResponse([
            'token' => $jwt['token'],
            'refresh_token' => $jwt['refresh_token'],
        ]);
    }
}
