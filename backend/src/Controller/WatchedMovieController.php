<?php

namespace App\Controller;

use DateTime;
use DateTimeImmutable;
use App\Entity\WatchedMovie;
use App\Service\WatchedMovieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/movies')]
final class WatchedMovieController extends AbstractController
{
    #[Route('/add', name: 'add_watched_movie', methods: ['POST'])]
    public function addWatchedMovie(Request $request, WatchedMovieService $watchedMovieService): JsonResponse
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $tmdbId = $data['tmdbId'] ?? null;

        if (!$tmdbId) {
            return new JsonResponse(['message' => 'tmdbId is required'], 400);
        }

        $WatchedMovie = $watchedMovieService->add($user, $tmdbId);

        if (!$WatchedMovie) {
            return new JsonResponse(['message' => 'Movie already added'], 400);
        }

        return new JsonResponse([
            'message' => 'Movie added successfully'
        ], 201);
    }

    #[Route('/watched', name: 'movie_mark_as_watched', methods: ['POST'])]
    public function markAsWatched(Request $request, WatchedMovieService $watchedMovieService): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        $tmdbId = $data['tmdbId'] ?? null;
        if (!$tmdbId) {
            return new JsonResponse(['message' => 'tmdbId is required'], 400);
        }

        $watchedMovie = $watchedMovieService->markAsWatched($user, $tmdbId);

        return new JsonResponse([
            'message' => 'Movie ' . $tmdbId . ' marked as watched',
            'watchCount' => $watchedMovie->getWatchCount(),
        ], 200);
    }

    #[Route('/delete/{id}', name: 'delete_watched_movie', methods: ['DELETE'])]
    public function deleteWatchedMovie(int $id, WatchedMovieService $watchedMovieService): JsonResponse
    {
        $user = $this->getUser();

        $result = $watchedMovieService->deleteWatchedMovie($user, $id);

        if ($result === null) {
            return new JsonResponse(['message' => 'Movie not found'], 404);
        } elseif ($result === false) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        } else {
            return new JsonResponse(['message' => 'Movie deleted successfully'], 200);
        }
    }
}
