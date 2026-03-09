<?php

namespace App\EventListener;

use App\Entity\RefreshToken;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Remove existing refresh tokens for this user
        $existingTokens = $this->em->getRepository(RefreshToken::class)->findBy(['user' => $user]);
        foreach ($existingTokens as $token) {
            $this->em->remove($token);
        }

        // Generate new refresh token
        $refreshTokenValue = bin2hex(random_bytes(64)); // raw token sent to client
        $hashedRefreshToken = hash('sha256', $refreshTokenValue); // hashed token stored in DB

        $refresh = new RefreshToken();
        $refresh->setToken($hashedRefreshToken);
        $refresh->setUser($user);
        $refresh->setExpiresAt(new DateTimeImmutable('+30 days'));

        $this->em->persist($refresh);
        $this->em->flush();

        // Add refresh token to response
        $data['refresh_token'] = $refreshTokenValue;

        $event->setData($data);
    }
}
