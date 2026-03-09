<?php

namespace App\Service;

use App\Entity\RefreshToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthService
{
    private EntityManagerInterface $em;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->em = $em;
        $this->jwtManager = $jwtManager;
    }

    public function refreshToken(string $refreshToken): ?array
    {
        $hashedToken = hash('sha256', $refreshToken);

        $existing = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $hashedToken]);

        if (!$existing || $existing->getExpiresAt() < new DateTimeImmutable()) {
            return null; // invalid or expired
        }

        $user = $existing->getUser();

        $this->em->remove($existing);// Remove old token

        // Create new refresh token
        $newRawToken = bin2hex(random_bytes(64));
        $hashedNewToken = hash('sha256', $newRawToken);

        $newRefresh = new RefreshToken();
        $newRefresh->setToken($hashedNewToken);
        $newRefresh->setUser($user);
        $newRefresh->setExpiresAt(new DateTimeImmutable('+30 days'));

        $this->em->persist($newRefresh);
        $this->em->flush();

        
        $jwt = $this->jwtManager->create($user); // Generate new JWT

        return [
            'token' => $jwt,
            'refresh_token' => $newRawToken,
        ];
    }
}
