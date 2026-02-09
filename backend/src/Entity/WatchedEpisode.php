<?php

namespace App\Entity;

use App\Repository\WatchedEpisodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatchedEpisodeRepository::class)]
class WatchedEpisode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $episodeId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $watchedAt = null;

    #[ORM\ManyToOne(inversedBy: 'watchedEpisodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WatchedShow $watchedShow = null;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEpisodeId(): ?int
    {
        return $this->episodeId;
    }

    public function setEpisodeId(int $episodeId): static
    {
        $this->episodeId = $episodeId;

        return $this;
    }

    public function getWatchedAt(): ?\DateTimeImmutable
    {
        return $this->watchedAt;
    }

    public function setWatchedAt(\DateTimeImmutable $watchedAt): static
    {
        $this->watchedAt = $watchedAt;

        return $this;
    }

    public function getWatchedShow(): ?WatchedShow
    {
        return $this->watchedShow;
    }

    public function setWatchedShow(?WatchedShow $watchedShow): static
    {
        $this->watchedShow = $watchedShow;

        return $this;
    }
}
