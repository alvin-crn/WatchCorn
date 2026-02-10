<?php

namespace App\Controller;

use App\Entity\WatchedShow;
use App\Entity\WatchedEpisode;
use App\Service\WatchedShowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/shows')]
final class WatchedShowController extends AbstractController
{
    #[Route('/add', name: 'add_watched_show', methods: ['POST'])]
    public function addWatchedShow(Request $request, WatchedShowService $watchedShowService): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $tmdbId = $data['tmdbId'] ?? null;

        if (!$tmdbId) {
            return new JsonResponse(['message' => 'tmdbId is required'], 400);
        }

        $added = $watchedShowService->addShow($user, $tmdbId);

        if (!$added) {
            return new JsonResponse(['message' => 'Show already added'], 409);
        }

        return new JsonResponse(['message' => 'Show added successfully'], 201);
    }

    #[Route('/delete/{id}', name: 'delete_watched_show', methods: ['DELETE'])]
    public function deleteWatchedShow(int $id, WatchedShowService $watchedShowService): JsonResponse
    {
        $user = $this->getUser();

        $result = $watchedShowService->deleteShow($user, $id);

        if ($result === null) {
            return new JsonResponse(['message' => 'Show not found'], 404);
        }

        if ($result === false) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse(['message' => 'Show deleted successfully', 'id' => $id], 200);
    }

    #[Route('/episode/watch', name: 'watched_episode', methods: ['POST'])]
    public function watchedEpisode(Request $request, WatchedShowService $watchedShowService): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $tmdbId = $data['tmdbId'] ?? null;
        $showId = $data['showId'] ?? null;

        if (!$tmdbId || !$showId) {
            return new JsonResponse(['message' => 'tmdbId and showId are required'], 400);
        }

        $result = $watchedShowService->watchEpisode($user, $showId, $tmdbId);

        return new JsonResponse([
            'message' => 'Episode processed',
            'status' => $result['status'],
            'watchCount' => $result['watchCount']
        ], 200);
    }

    #[Route('/episode/unwatch/{id}', name: 'unwatch_episode', methods: ['DELETE'])]
    public function unwatchEpisode(int $id, WatchedShowService $watchedShowService): JsonResponse
    {
        $user = $this->getUser();
        $result = $watchedShowService->unwatchEpisode($user, $id);

        if ($result === null) {
            return new JsonResponse(['message' => 'Episode not found'], 404);
        }

        if ($result === false) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse(['message' => 'Episode deleted successfully', 'id' => $id], 200);
    }
}
