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

    public function usernameValidator(?string $username): ?string
    {
        if (!$username) {
            return 'Nom d’utilisateur requis.';
        }
        if (strlen($username) < 3) {
            return 'Minimum 3 caractères pour le nom d’utilisateur.';
        }
        if (strlen($username) > 20) {
            return 'Maximum 20 caractères pour le nom d’utilisateur.';
        }
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return 'Caractères invalides (lettres, chiffres, underscore et tiret).';
        }
        if ($this->usernameExists($username)) {
            return 'Nom d’utilisateur déjà pris.';
        }
        return null;
    }

    public function emailValidator(?string $email): ?string
    {
        if (!$email) {
            return 'Email requis.';
        }
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
            return 'Email invalide (ex: user@exemple.com).';
        }
        if ($this->emailExists($email)) {
            return 'Email déjà utilisé.';
        }
        return null;
    }

    public function passwordValidator(?string $password): ?string
    {
        if (!$password) {
            return 'Mot de passe requis.';
        }
        if (strlen($password) < 12) {
            return 'Minimum 12 caractères pour le mot de passe.';
        }
        if (strlen($password) > 128) {
            return 'Maximum 128 caractères pour le mot de passe.';
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+=]).+$/', $password)) {
            return 'Le mot de passe doit contenir au moins : 1 majuscule, 1 minuscule, 1 chiffre et 1 symbole parmi ! @ # $ % ^ & * ( ) _ - + =';
        }
        return null;
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
