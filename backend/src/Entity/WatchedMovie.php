<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\WatchedMovieRepository;

#[ORM\Entity(repositoryClass: WatchedMovieRepository::class)]
class WatchedMovie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'watchedMovies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $watchedAt = null;

    #[ORM\Column]
    private ?int $movieId = null;

    #[ORM\Column(nullable: true)]
    private ?int $watchCount = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getWatchedAt(): ?\DateTimeImmutable
    {
        return $this->watchedAt;
    }

    public function setWatchedAt(?\DateTimeImmutable $watchedAt): static
    {
        $this->watchedAt = $watchedAt;

        return $this;
    }

    public function getMovieId(): ?int
    {
        return $this->movieId;
    }

    public function setMovieId(int $movieId): static
    {
        $this->movieId = $movieId;

        return $this;
    }

    public function getWatchCount(): ?int
    {
        return $this->watchCount;
    }

    public function setWatchCount(int $watchCount): static
    {
        $this->watchCount = $watchCount;

        return $this;
    }
}
