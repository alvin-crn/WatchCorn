<?php

namespace App\Controller;

use App\Entity\WatchedMovie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTime;
use DateTimeImmutable;

#[Route('/api/movies')]
final class WatchedMovieController extends AbstractController
{
    #[Route('/add', name: 'add_watched_movie', methods: ['POST'])]
    public function addWatchedMovie(Request $request, EntityManagerInterface $em): JsonResponse
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

        $existing = $em->getRepository(WatchedMovie::class)->findOneBy([
            'User' => $user,
            'movieId' => $tmdbId,
        ]);

        if ($existing) {
            return new JsonResponse(
                ['message' => 'Movie already added'],
                409
            );
        }

        $movie = new WatchedMovie();
        $movie->setUser($user);
        $movie->setMovieId($tmdbId);
        $em->persist($movie);
        $em->flush();

        return new JsonResponse([
            'message' => 'Movie added successfully'
        ], 201);
    }

    #[Route('/watched', name: 'watched_movies', methods: ['POST'])]
    public function watchedMovie(Request $request, EntityManagerInterface $em): JsonResponse
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

        $watchedMovie = $em->getRepository(WatchedMovie::class)->findOneBy([
            'User' => $user,
            'movieId' => $tmdbId,
        ]);

        // Si le film est ajouté mais pas encore marqué comme vu, le marquer comme vu
        if ($watchedMovie && !$watchedMovie->getWatchedAt()) {
            $watchedMovie->setWatchedAt(new DateTimeImmutable());
            $watchedMovie->setWatchCount(1);
            $em->flush();

            return new JsonResponse(
                ['message' => 'Movie marked as watched'],
                200
            );
        } elseif ($watchedMovie && $watchedMovie->getWatchedAt() != null) {
            // Si le film est déjà marqué comme vu, on incrémente le compteur de visionnage
            $watchedMovie->setWatchCount($watchedMovie->getWatchCount() + 1);
            $em->flush();

            return new JsonResponse(
                [
                    'message' => 'Movie watched again',
                    'watchCount' => $watchedMovie->getWatchCount(),
                ],
                200
            );
        } else {
            // Si le film n'est pas dans la liste de l'utilisateur, on l'ajoute et le marque comme vu
            $watchedMovie = new WatchedMovie();
            $watchedMovie->setUser($user);
            $watchedMovie->setMovieId($tmdbId);
            $watchedMovie->setWatchedAt(new DateTimeImmutable());
            $watchedMovie->setWatchCount(1);
            $em->persist($watchedMovie);
            $em->flush();
            return new JsonResponse(
                ['message' => 'Movie added and marked as watched'],
                201
            );
        }
    }

    #[Route('/delete/{id}', name: 'delete_watched_movie', methods: ['DELETE'])]
    public function deleteWatchedMovie(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        $movie = $em->getRepository(WatchedMovie::class)->find($id);
        if (!$movie) {
            return new JsonResponse(['message' => 'WatchedMovie not found'], 404);
        }

        if ($movie->getUser() !== $user) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $em->remove($movie);
        $em->flush();

        return new JsonResponse([
            'message' => 'Movie deleted successfully',
            'id' => $id
        ], 200);
    }
}
