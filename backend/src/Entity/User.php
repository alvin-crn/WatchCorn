<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $displayName = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $restricted = false;

    #[ORM\Column]
    private ?bool $actived = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePic = null;

    /**
     * @var Collection<int, WatchedShow>
     */
    #[ORM\OneToMany(targetEntity: WatchedShow::class, mappedBy: 'User', orphanRemoval: true)]
    private Collection $watchedShows;

    /**
     * @var Collection<int, WatchedMovie>
     */
    #[ORM\OneToMany(targetEntity: WatchedMovie::class, mappedBy: 'User', orphanRemoval: true)]
    private Collection $watchedMovies;

    /**
     * @var Collection<int, RefreshToken>
     */
    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'user')]
    private Collection $refreshTokens;

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'follower', orphanRemoval: true)]
    private Collection $following;

    /**
     * @var Collection<int, Follow>
     */
    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'following', orphanRemoval: true)]
    private Collection $followers;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->createdAt = new \DateTimeImmutable();
        $this->watchedShows = new ArrayCollection();
        $this->watchedMovies = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
    }

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isRestricted(): ?bool
    {
        return $this->restricted;
    }

    public function setRestricted(bool $restricted): static
    {
        $this->restricted = $restricted;

        return $this;
    }

    public function getProfilePic(): ?string
    {
        if (!$this->profilePic) {
            $this->profilePic = 'default-profile-pic.png';
        }

        return $this->profilePic;
    }

    public function setProfilePic(?string $profilePic): static
    {
        $this->profilePic = $profilePic;

        return $this;
    }

    /**
     * @return Collection<int, WatchedShow>
     */
    public function getWatchedShows(): Collection
    {
        return $this->watchedShows;
    }

    public function addWatchedShow(WatchedShow $watchedShow): static
    {
        if (!$this->watchedShows->contains($watchedShow)) {
            $this->watchedShows->add($watchedShow);
            $watchedShow->setUser($this);
        }

        return $this;
    }

    public function removeWatchedShow(WatchedShow $watchedShow): static
    {
        if ($this->watchedShows->removeElement($watchedShow)) {
            // set the owning side to null (unless already changed)
            if ($watchedShow->getUser() === $this) {
                $watchedShow->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WatchedMovie>
     */
    public function getWatchedMovies(): Collection
    {
        return $this->watchedMovies;
    }

    public function addWatchedMovie(WatchedMovie $watchedMovie): static
    {
        if (!$this->watchedMovies->contains($watchedMovie)) {
            $this->watchedMovies->add($watchedMovie);
            $watchedMovie->setUser($this);
        }

        return $this;
    }

    public function removeWatchedMovie(WatchedMovie $watchedMovie): static
    {
        if ($this->watchedMovies->removeElement($watchedMovie)) {
            // set the owning side to null (unless already changed)
            if ($watchedMovie->getUser() === $this) {
                $watchedMovie->setUser(null);
            }
        }

        return $this;
    }

    public function isActived(): ?bool
    {
        return $this->actived;
    }

    public function setActived(bool $actived): static
    {
        $this->actived = $actived;

        return $this;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): static
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setUser($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): static
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            // set the owning side to null (unless already changed)
            if ($refreshToken->getUser() === $this) {
                $refreshToken->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(Follow $following): static
    {
        if (!$this->following->contains($following)) {
            $this->following->add($following);
            $following->setFollower($this);
        }

        return $this;
    }

    public function removeFollowing(Follow $following): static
    {
        if ($this->following->removeElement($following)) {
            // set the owning side to null (unless already changed)
            if ($following->getFollower() === $this) {
                $following->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follow $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->setFollowing($this);
        }

        return $this;
    }

    public function removeFollower(Follow $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            // set the owning side to null (unless already changed)
            if ($follower->getFollowing() === $this) {
                $follower->setFollowing(null);
            }
        }

        return $this;
    }
}