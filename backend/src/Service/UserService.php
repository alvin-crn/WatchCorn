<?php

namespace App\Service;

use App\Entity\User;
use App\Service\TmdbService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private TmdbService $tmdbService
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
    public function fullProfile(User $user): array
    {
        // Récupérer les détails des séries regardées
        $watchedShows = [];
        foreach ($user->getWatchedShows() as $show) {
            // Récupérer les détails de chaque épisode regardé pour cette série
            $episodes = [];
            foreach ($show->getWatchedEpisodes() as $episode) {
                $episodes[] = [
                    'id' => $episode->getId(),
                    'episodeId' => $episode->getEpisodeId(),
                    'watchedAt' => $episode->getWatchedAt(),
                    'watchedAt' => $episode->getWatchedAt()->format('c'),
                    'watchCount' => $episode->getWatchCount(),
                ];
            }

            // Ajouter les détails de la série et de ses épisodes à la liste des séries regardées
            $tmdbShowData = $this->tmdbService->getTvShowById($show->getShowId());
            $watchedShows[] = [
                'id' => $show->getId(),
                'showId' => $show->getShowId(),
                'addedAt' => $show->getAddedAt()->format('c'),
                'episodes' => $episodes,
                'tmdbPoster' => $tmdbShowData['poster_path'] ?? null, // Ajouter le poster de la série depuis TMDb
                'tmdbName' => $tmdbShowData['name'] ?? null, // Ajouter le nom de la série depuis TMDb
            ];
        }

        // Récupérer les détails des films regardés
        $watchedMovies = [];
        foreach ($user->getWatchedMovies() as $movie) {
            $tmdbMovieData = $this->tmdbService->getMovieById($movie->getMovieId());
            $watchedMovies[] = [
                'id' => $movie->getId(),
                'movieId' => $movie->getMovieId(),
                'addedAt' => $movie->getAddedAt()->format('c'),
                'watchedAt' => $movie->getWatchedAt()->format('c'),
                'watchCount' => $movie->getWatchCount(),
                'tmdbPoster' => $tmdbMovieData['poster_path'] ?? null, // Ajouter le poster du film depuis TMDb
                'tmdbName' => $tmdbMovieData['title'] ?? null, // Ajouter le titre du film depuis TMDb
            ];
        }

        // Construire le profil complet de l'utilisateur avec les détails des séries et films regardés
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('c'),
            'restricted' => $user->isRestricted(),
            'profilePic' => $user->getProfilePic(),
            'watchedMovies' => $watchedMovies,
            'watchedShows' => $watchedShows,
        ];
    }

    public function publicProfile(User $user): array
    {
        return [
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'profilePic' => $user->getProfilePic(),
            'createdAt' => $user->getCreatedAt()->format('c'),
            'watchedShows' => array_map(
                fn($show) => [
                    'showId' => $show->getShowId(),
                    'tmdbPoster' => $this->tmdbService->getTvShowById($show->getShowId())['poster_path'] ?? null,
                    'tmdbName' => $this->tmdbService->getTvShowById($show->getShowId())['name'] ?? null,
                ],
                $user->getWatchedShows()->toArray()
            ),

            'watchedMovies' => array_map(
                fn($movie) => [
                    'movieId' => $movie->getMovieId(),
                    'tmdbPoster' => $this->tmdbService->getMovieById($movie->getMovieId())['poster_path'] ?? null,
                    'tmdbName' => $this->tmdbService->getMovieById($movie->getMovieId())['title'] ?? null,
                ],
                $user->getWatchedMovies()->toArray()
            ),
        ];
    }
}
