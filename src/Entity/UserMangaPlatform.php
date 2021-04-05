<?php

namespace App\Entity;

use App\Repository\UserMangaPlatformRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

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
     * @ORM\ManyToOne(targetEntity=MangaPlatform::class, inversedBy="userMangaPlatforms")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "mangaList" })
     */
    private $mangaPlatform;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userMangaPlatforms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Chapter::class)
     * @Serializer\Groups({ "mangaList" })
     */
    private $lastChapter;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $lastPage;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Groups({ "mangaList" })
     */
    private $favorite = false;

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
