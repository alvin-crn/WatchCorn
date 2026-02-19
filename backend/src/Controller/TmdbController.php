<?php

namespace App\Controller;

use App\Service\TmdbService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tmdb')]
final class TmdbController extends AbstractController
{
    #[Route('/search', methods: ['GET'])]
    public function search(TmdbService $tmdbService): JsonResponse
    {
        $query = $_GET['q'] ?? null;

        if (!$query) {
            return new JsonResponse(['message' => 'Query is required'], 400);
        }

        return new JsonResponse($tmdbService->search($query));
    }
}
