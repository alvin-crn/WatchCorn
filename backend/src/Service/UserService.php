<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function createUser(string $username, string $email, string $plainPassword): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setDisplayName($username);
        $user->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function usernameExists(string $username): bool
    {
        return (bool) $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function emailExists(string $email): bool
    {
        return (bool) $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function presentMe(User $user): array
    {
        $watchedShows = [];
        foreach ($user->getWatchedShows() as $show) {
            $episodes = [];
            foreach ($show->getWatchedEpisodes() as $episode) {
                $episodes[] = [
                    'id' => $episode->getId(),
                    'episodeId' => $episode->getEpisodeId(),
                    'watchedAt' => $episode->getWatchedAt(),
                    'watchCount' => $episode->getWatchCount(),
                ];
            }

            $watchedShows[] = [
                'id' => $show->getId(),
                'showId' => $show->getShowId(),
                'addedAt' => $show->getAddedAt(),
                'episodes' => $episodes,
            ];
        }

        $watchedMovies = [];
        foreach ($user->getWatchedMovies() as $movie) {
            $watchedMovies[] = [
                'id' => $movie->getId(),
                'movieId' => $movie->getMovieId(),
                'addedAt' => $movie->getAddedAt(),
                'watchedAt' => $movie->getWatchedAt(),
                'watchCount' => $movie->getWatchCount(),
            ];
        }

        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt(),
            'restricted' => $user->isRestricted(),
            'profilePic' => $user->getProfilePic(),
            'watchedMovies' => $watchedMovies,
            'watchedShows' => $watchedShows,
        ];
    }

    public function presentPublic(User $user): array
    {
        return [
            'username' => $user->getUsername(),
            'profilePic' => $user->getProfilePic(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'watchedShows' => array_map(
                fn($show) => ['showId' => $show->getShowId()],
                $user->getWatchedShows()->toArray()
            ),
            'watchedMovies' => array_map(
                fn($movie) => ['movieId' => $movie->getMovieId()],
                $user->getWatchedMovies()->toArray()
            ),
        ];
    }
}
