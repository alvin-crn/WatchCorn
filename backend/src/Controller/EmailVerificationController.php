<?php

namespace App\Controller;

use App\Entity\EmailVerification;
use App\Entity\User;
use App\Service\EmailVerificationService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'api_')]
final class EmailVerificationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EmailVerificationService $emailVerificationService,
        private readonly MailService $mailService
    ) {}
    
    #[Route('verify-email', name: 'verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        $tokenValue = $request->query->get('token');

        if (!$tokenValue) {
            return new JsonResponse(['message' => 'Ce lien est invalide.'], 400);
        }

        $token = $this->em->getRepository(EmailVerification::class)->findOneBy(['token' => $tokenValue]);

        if (!$token) {
            return new JsonResponse(['message' => 'Ce lien est invalide.'], 400);
        }

        if ($token->getExpiresAt() < new \DateTimeImmutable()) {
            return new JsonResponse(['message' => 'Ce lien a expiré.'], 400);
        }

        $user = $token->getUser();
        $user->setActived(true);

        $this->em->remove($token);
        $this->em->flush();

        return new JsonResponse(['message' => 'Compte activé.']);
    }

    #[Route('send-email-verification', name: 'send_email_verification', methods: ['POST'])]
    public function sendEmailVerification(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;

        if (!$username) {
            return new JsonResponse(['message' => 'Aucun utilisateur à qui envoyer l\'email de vérification'], 400);
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur inexistant'], 404);
        }

        if ($user->isActived()) {
            return new JsonResponse(['message' => 'Ce compte est déjà activé.'], 400);
        }

        // Supprimer les anciens tokens de vérification pour cet utilisateur
        $existingTokens = $this->em->getRepository(EmailVerification::class)->findBy(['User' => $user]);
        if ($existingTokens) {
            foreach ($existingTokens as $existingToken) {
                $this->em->remove($existingToken);
            }
            $this->em->flush();
        }

        $token = $this->emailVerificationService->generateUrlVerification($user);

        $content = "
        <h1>Je verifie mon email 📧</h1> 
        Afin d'activer votre compte, veuillez confirmer votre adresse email en cliquant sur le lien suivant : <a href='https://watchcorn.alvincrn.fr/compte-active?token=" . $token . "'>Confirmer mon email</a> </br></br>
        Ce lien expire dans 30 minutes.
        ";
        $this->mailService->sendEmail($user->getEmail(), "J'active mon compte WatchCorn !", $content);

        return new JsonResponse(['message' => 'Email de vérification envoyé à ' . $user->getEmail()], 201);
    }
}