<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\User;
use App\Service\EmailVerificationService;
use App\Service\MailService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'api_')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserService $userService,
        private readonly EmailVerificationService $emailVerificationService,
        private readonly MailService $mailService
    ) {}

    #[Route('register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$email || !$password) {
            return new JsonResponse(['message' => 'Champs manquants'], 400);
        }

        $usernameError = $this->userService->usernameValidator($username);
        if ($usernameError) return new JsonResponse(['message' => $usernameError], 400);

        $emailError = $this->userService->emailValidator($email);
        if ($emailError) return new JsonResponse(['message' => $emailError], 400);

        $passwordError = $this->userService->passwordValidator($password);
        if ($passwordError) return new JsonResponse(['message' => $passwordError], 400);

        $user = $this->userService->createUser($username, $email, $password);

        $token = $this->emailVerificationService->generateUrlVerification($user);

        // Envoyer un email de confirmation d'inscription
        $content = "
        <h1>Bienvenue sur WatchCorn🍿</h1> 
        Nous vous remercions pour votre inscription, <strong>" . $username . "</strong> !</br></br>
        Afin d'activer votre compte, veuillez confirmer votre adresse email en cliquant sur le lien suivant : <a href='https://watchcorn.alvincrn.fr/compte-active?token=" . $token . "'>Confirmer mon email</a> </br></br>
        Ce lien expire dans 30 minutes.
        ";
        $this->mailService->sendEmail($user->getEmail(), "J'active mon compte WatchCorn !", $content);

        return new JsonResponse(['message' => 'User created successfully'], 201);
    }

    #[Route('me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse([
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'restricted' => $user->isRestricted(),
            'profilePic' => $user->getProfilePic(),
        ]);
    }

    #[Route('user/{username}', name: 'user_profile', methods: ['GET'])]
    public function getUserProfile(string $username): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur introuvable'], 404);
        }

        $profile = $this->userService->publicProfile($user, $this->getUser());

        return new JsonResponse($profile);
    }

    #[Route('/user/{username}', name: 'user_update', methods: ['PUT'])]
    public function updateProfile(string $username, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();

        // Sécurité : seul le propriétaire peut modifier son profil
        if (!$currentUser || $currentUser->getUsername() !== $username) {
            return new JsonResponse(['message' => 'Accès interdit'], 403);
        }

        // Récupération des données envoyées
        $displayName = $request->request->get('displayName');
        $photo = $request->files->get('photo');

        // Si rien n'est envoyé
        if (!$displayName && !$photo) {
            return new JsonResponse(['message' => 'Aucune modification envoyée'], 400);
        }

        // Si displayName est envoyé → on valide
        if ($displayName !== null) {
            $displayNameError = $this->userService->displayNameValidator($displayName);
            if ($displayNameError) {
                return new JsonResponse(['message' => $displayNameError], 400);
            }
        }

        // Appel au service pour mettre à jour le user
        try {
            $this->userService->updateUser($currentUser, [
                'displayName' => $displayName,
                'photo' => $photo
            ]);

            return new JsonResponse(['message' => 'Profil mis à jour avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    #[Route('/user/follow/{username}', name: 'user_follow', methods: ['POST'])]
    public function followUser(string $username): JsonResponse
    {
        // Vérifier que l'utilisateur est authentifié
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['message' => 'Authentification requise'], 401);
        }

        // Vérifier que l'utilisateur à suivre existe
        $userToFollow = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$userToFollow) {
            return new JsonResponse(['message' => 'Utilisateur à suivre introuvable'], 404);
        }

        // Vérifier que l'utilisateur ne tente pas de se suivre lui-même
        if ($userToFollow === $currentUser) {
            return new JsonResponse(['message' => 'Vous ne pouvez pas vous suivre vous-même'], 400);
        }

        // Vérifier si le follow existe déjà
        $existingFollow = $this->em->getRepository(Follow::class)->findOneBy([
            'follower' => $currentUser,
            'following' => $userToFollow,
        ]);

        if ($existingFollow) {
            return new JsonResponse(['message' => 'Vous suivez déjà cet utilisateur'], 400);
        }

        // Créer le follow
        $follow = new Follow();
        $follow->setFollower($currentUser);
        $follow->setFollowing($userToFollow);

        $this->em->persist($follow);
        $this->em->flush();

        return new JsonResponse(['message' => 'Utilisateur suivi avec succès']);
    }

    #[Route('/user/unfollow/{username}', name: 'user_unfollow', methods: ['POST'])]
    public function unfollowUser(string $username): JsonResponse
    {
        // Vérifier que l'utilisateur est authentifié
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['message' => 'Authentification requise'], 401);
        }

        // Vérifier que l'utilisateur à ne plus suivre existe
        $userToUnfollow = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$userToUnfollow) {
            return new JsonResponse(['message' => 'Utilisateur à ne plus suivre introuvable'], 404);
        }

        // Vérifier que l'utilisateur ne tente pas de se désabonner de lui-même
        if ($userToUnfollow === $currentUser) {
            return new JsonResponse(['message' => 'Vous ne pouvez pas vous désabonner de vous-même'], 400);
        }

        // Chercher le follow existant
        $follow = $this->em->getRepository(Follow::class)->findOneBy([
            'follower' => $currentUser,
            'following' => $userToUnfollow,
        ]);

        if (!$follow) {
            return new JsonResponse(['message' => 'Vous ne suivez pas cet utilisateur'], 400);
        }

        // Supprimer le follow
        $this->em->remove($follow);
        $this->em->flush();

        return new JsonResponse(['message' => 'Utilisateur désabonné avec succès']);
    }
}
