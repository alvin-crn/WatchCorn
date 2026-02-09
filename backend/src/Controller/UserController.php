<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class UserController extends AbstractController
{
    #[Route('/api/me', name: 'app_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        // Watched Shows
        $watchedShows = [];
        foreach ($user->getWatchedShows() as $show) {
            // Watched Episodes for each show
            $episodes = [];
            foreach ($show->getWatchedEpisodes() as $episode) {
                $episodes[] = [
                    'id' => $episode->getId(),
                    'episodeId' => $episode->getEpisodeId(),
                    'watchedAt' => $episode->getWatchedAt(),
                ];
            }

            $watchedShows[] = [
                'id' => $show->getId(),
                'showId' => $show->getShowId(),
                'addedAt' => $show->getAddedAt(),
                'episodes' => $episodes,
            ];
        }

        // Watched Movies
        $watchedMovies = [];
        foreach ($user->getWatchedMovies() as $movie) {
            $watchedMovies[] = [
                'id' => $movie->getId(),
                'movieId' => $movie->getMovieId(),
                'addedAt' => $movie->getAddedAt(),
                'watchedAt' => $movie->getWatchedAt(),
            ];
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt(),
            'restricted' => $user->isRestricted(),
            'profilePic' => $user->getProfilePic(),
            'watchedMovies' => $watchedMovies,
            'watchedShows' => $watchedShows,
        ]);
    }

    #[Route('/api/user/{username}', name: 'app_user', methods: ['GET'])]
    public function getUserProfile(string $username, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(
                ['message' => 'User not found'],
                404
            );
        }

        $watchedShows = [];
        foreach ($user->getWatchedShows() as $show) {
            $watchedShows[] = [
                'showId' => $show->getShowId()
            ];
        }

        $watchedMovies = [];
        foreach ($user->getWatchedMovies() as $movie) {
            $watchedMovies[] = [
                'movieId' => $movie->getMovieId()
            ];
        }

        return new JsonResponse([
            'username' => $user->getUsername(),
            'profilePic' => $user->getProfilePic(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'watchedShows' => $watchedShows,
            'watchedMovies' => $watchedMovies,
        ]);
    }
}
