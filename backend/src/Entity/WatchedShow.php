<?php

namespace App\Entity;

use App\Repository\WatchedShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WatchedShowRepository::class)]
class WatchedShow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'watchedShows')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    #[ORM\Column]
    private ?int $showId = null;

    /**
     * @var Collection<int, WatchedEpisode>
     */
    #[ORM\OneToMany(targetEntity: WatchedEpisode::class, mappedBy: 'watchedShow', orphanRemoval: true)]
    private Collection $watchedEpisodes;

    public function __construct()
    {
        $this->watchedEpisodes = new ArrayCollection();
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

    public function getShowId(): ?int
    {
        return $this->showId;
    }

    public function setShowId(int $showId): static
    {
        $this->showId = $showId;

        return $this;
    }

    /**
     * @return Collection<int, WatchedEpisode>
     */
    public function getWatchedEpisodes(): Collection
    {
        return $this->watchedEpisodes;
    }

    public function addWatchedEpisode(WatchedEpisode $watchedEpisode): static
    {
        if (!$this->watchedEpisodes->contains($watchedEpisode)) {
            $this->watchedEpisodes->add($watchedEpisode);
            $watchedEpisode->setWatchedShow($this);
        }

        return $this;
    }

    public function removeWatchedEpisode(WatchedEpisode $watchedEpisode): static
    {
        if ($this->watchedEpisodes->removeElement($watchedEpisode)) {
            // set the owning side to null (unless already changed)
            if ($watchedEpisode->getWatchedShow() === $this) {
                $watchedEpisode->setWatchedShow(null);
            }
        }

        return $this;
    }
}
