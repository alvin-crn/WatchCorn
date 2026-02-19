<?php

namespace App\Service;

use App\Entity\EmailVerification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class EmailVerificationService
{
  private EntityManagerInterface $em;

  public function __construct(EntityManagerInterface $em)
  {
    $this->em = $em;
  }

  public function generateUrlVerification(User $user): string
  {
    $token = bin2hex(random_bytes(32));

    $verificationToken = new EmailVerification();
    $verificationToken->setToken($token);
    $verificationToken->setUser($user);
    $verificationToken->setExpiresAt(new \DateTimeImmutable('+30 minutes'));

    $this->em->persist($verificationToken);
    $this->em->flush();

    return $token;
  }
}
