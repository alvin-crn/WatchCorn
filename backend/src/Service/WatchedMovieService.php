<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\WatchedMovie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\HttpFoundation\JsonResponse;

class WatchedMovieService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function add(User $user, int $tmdbId): ?WatchedMovie
    {
        $watchedMovie = $this->findByUserAndTmdbId($user, $tmdbId);

        if ($watchedMovie) {
            return null; // Movie already exists for this user
        }

        $movie = new WatchedMovie();
        $movie->setUser($user);
        $movie->setMovieId($tmdbId);

        $this->em->persist($movie);
        $this->em->flush();

        return $movie;
    }

    public function markAsWatched(User $user, int $tmdbId): WatchedMovie
    {
        $movie = $this->findByUserAndTmdbId($user, $tmdbId);

        if (!$movie) {
            $movie = new WatchedMovie();
            $movie->setUser($user);
            $movie->setMovieId($tmdbId);
            $movie->setWatchCount(0);
            $this->em->persist($movie);
        }

        if (!$movie->getWatchedAt()) {
            $movie->setWatchedAt(new DateTimeImmutable());
            $movie->setWatchCount(1);
        } else {
            $movie->setWatchCount($movie->getWatchCount() + 1);
        }

        $this->em->flush();

        return $movie;
    }

    public function deleteWatchedMovie(User $user, int $id): ?bool
    {
        $movie = $this->em->getRepository(WatchedMovie::class)->find($id);

        if (!$movie) {
            return null; // Not found
        }

        if ($movie->getUser() !== $user) {
            return false; // Forbidden
        }

        $this->em->remove($movie);
        $this->em->flush();

        return true;
    }

    private function findByUserAndTmdbId(User $user, int $tmdbId): ?WatchedMovie
    {
        return $this->em->getRepository(WatchedMovie::class)->findOneBy([
            'User' => $user,
            'movieId' => $tmdbId,
        ]);
    }
}
