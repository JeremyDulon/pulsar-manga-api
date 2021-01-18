<?php

namespace App\Entity;

use App\Repository\UserMangaPlatformRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserMangaPlatformRepository::class)
 */
class UserMangaPlatform
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=MangaPlatform::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $mangaPlatform;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userMangaPlatforms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Chapter::class)
     */
    private $lastChapter;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastPage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $favorite;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMangaPlatform(): ?MangaPlatform
    {
        return $this->mangaPlatform;
    }

    public function setMangaPlatform(?MangaPlatform $mangaPlatform): self
    {
        $this->mangaPlatform = $mangaPlatform;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getLastChapter(): ?Chapter
    {
        return $this->lastChapter;
    }

    public function setLastChapter(?Chapter $lastChapter): self
    {
        $this->lastChapter = $lastChapter;

        return $this;
    }

    public function getLastPage(): ?int
    {
        return $this->lastPage;
    }

    public function setLastPage(?int $lastPage): self
    {
        $this->lastPage = $lastPage;

        return $this;
    }

    /**
     * @return bool
     */
    public function getFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     * @return UserMangaPlatform
     */
    public function setFavorite(bool $favorite): UserMangaPlatform
    {
        $this->favorite = $favorite;
        return $this;
    }
}
