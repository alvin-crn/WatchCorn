<?php

namespace App\Controller;

use App\Entity\WatchedEpisode;
use App\Entity\WatchedShow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/watched_shows')]
final class WatchedShowController extends AbstractController
{
    #[Route('', name: 'add_watched_show', methods: ['POST'])]
    public function addWatchedShow(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $tmdbId = $data['tmdbId'] ?? null;

        if (!$tmdbId) {
            return new JsonResponse(
                ['message' => 'tmdbId is required'],
                400
            );
        }

        $existing = $em->getRepository(WatchedShow::class)->findOneBy([
            'User' => $user,
            'showId' => $tmdbId,
        ]);

        if ($existing) {
            return new JsonResponse(
                ['message' => 'Show already added'],
                409
            );
        }

        $show = new WatchedShow();
        $show->setUser($user);
        $show->setShowId($tmdbId);
        $em->persist($show);
        $em->flush();

        return new JsonResponse([
            'message' => 'Show added successfully'
        ], 201);
    }

    #[Route('/{id}', name: 'delete_watched_show', methods: ['DELETE'])]
    public function deleteWatchedShow(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $show = $em->getRepository(WatchedShow::class)->find($id);
        if (!$show) {
            return new JsonResponse(['message' => 'WatchedShow not found'], 404);
        }

        if ($show->getUser() !== $user) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $em->remove($show);
        $em->flush();

        return new JsonResponse([
            'message' => 'Show deleted successfully',
            'id' => $id
        ], 200);
    }

    #[Route('/episode', name: 'watched_episode', methods: ['POST'])]
    public function watchedEpisode(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $tmdbId = $data['tmdbId'] ?? null;
        $showId = $data['showId'] ?? null;

        if (!$tmdbId || !$showId) {
            return new JsonResponse(
                ['message' => 'tmdbId and showId are required'],
                400
            );
        }

        // Vérifier si ce show est déjà dans la liste de l'utilisateur
        $watchedShow = $em->getRepository(WatchedShow::class)->findOneBy([
            'User' => $user,
            'showId' => $showId,
        ]);

        // Si le WatchedShow n'existe pas, le créer
        if (!$watchedShow) {
            $watchedShow = new WatchedShow();
            $watchedShow->setUser($user);
            $watchedShow->setShowId($showId);
            $em->persist($watchedShow);
        } else {
            // Sinon vérifier si l'utilisateur a déjà vu cet épisode
            $existing = $em->getRepository(WatchedEpisode::class)->findOneBy([
                'watchedShow' => $watchedShow,
                'episodeId' => $tmdbId,
            ]);

            // Si oui, incrémenter le compteur de visionnage
            if ($existing) {
                $existing->setWatchCount($existing->getWatchCount() + 1);
                $em->flush();

                return new JsonResponse([
                    'message' => 'Episode watched again',
                    'watchCount' => $existing->getWatchCount(),
                ], 200);
            }
        }

        $episode = new WatchedEpisode(); // Créer un nouvel enregistrement pour cet épisode
        $episode->setEpisodeId($tmdbId); // ID de l'épisode vu
        $episode->setWatchedShow($watchedShow); // Associer l'épisode au show suivi
        $em->persist($episode);
        $em->flush();

        return new JsonResponse([
            'message' => 'Episode watched successfully'
        ], 201);
    }

    #[Route('/episode/{id}', name: 'unwatch_episode', methods: ['DELETE'])]
    public function unwatchEpisode(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $episode = $em->getRepository(WatchedEpisode::class)->find($id);
        if (!$episode) {
            return new JsonResponse(['message' => 'WatchedEpisode not found'], 404);
        }

        if ($episode->getWatchedShow()->getUser() !== $user) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $em->remove($episode);
        $em->flush();

        return new JsonResponse([
            'message' => 'Episode unwatched successfully'
        ], 200);
    }
}
