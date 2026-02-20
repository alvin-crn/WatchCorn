<?php

namespace App\Controller;

use App\Entity\EmailVerification;
use App\Entity\User;
use App\Service\EmailVerificationService;
use App\Service\MailService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class UserController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EmailVerificationService $emailVerificationService,
        MailService $mailService,
        UserService $userService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$email || !$password) {
            return new JsonResponse(['message' => 'Champs manquants'], 400);
        }

        $usernameError = $userService->usernameValidator($username);
        if ($usernameError) return new JsonResponse(['message' => $usernameError], 400);

        $emailError = $userService->emailValidator($email);
        if ($emailError) return new JsonResponse(['message' => $emailError], 400);

        $passwordError = $userService->passwordValidator($password);
        if ($passwordError) return new JsonResponse(['message' => $passwordError], 400);

        $user = $userService->createUser($username, $email, $password);

        $token = $emailVerificationService->generateUrlVerification($user);

        // Envoyer un email de confirmation d'inscription
        $content = "
        <h1>Bienvenue sur WatchCorn🍿</h1> 
        Nous vous remercions pour votre inscription, <strong>" . $username . "</strong> !</br></br>
        Afin d'activer votre compte, veuillez confirmer votre adresse email en cliquant sur le lien suivant : <a href='https://watchcorn.alvincrn.fr/actived-account?token=" . $token . "'>Confirmer mon email</a> </br></br>
        Ce lien expire dans 30 minutes.
        ";
        $mailService->sendEmail($user->getEmail(), "J'active mon compte WatchCorn !", $content);

        return new JsonResponse(['message' => 'User created successfully'], 201);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
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

    #[Route('/user/{username}', name: 'user_profile', methods: ['GET'])]
    public function getUserProfile(string $username, EntityManagerInterface $em, UserService $userService): JsonResponse
    {
        $user = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($userService->presentPublic($user));
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tokenValue = $request->query->get('token');

        if (!$tokenValue) {
            return new JsonResponse(['message' => 'Ce lien est invalide.'], 400);
        }

        $token = $em->getRepository(EmailVerification::class)->findOneBy(['token' => $tokenValue]);

        if (!$token) {
            return new JsonResponse(['message' => 'Ce lien est invalide.'], 400);
        }

        if ($token->getExpiresAt() < new \DateTimeImmutable()) {
            return new JsonResponse(['message' => 'Ce lien a expiré.'], 400);
        }

        $user = $token->getUser();
        $user->setActived(true);

        $em->remove($token);
        $em->flush();

        return new JsonResponse(['message' => 'Compte activé.']);
    }
}
