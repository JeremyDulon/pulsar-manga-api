<?php

namespace App\Entity;

use App\Repository\UserMangaRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=UserMangaLanguageRepository::class)
 */
class UserMangaLanguage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="userMangaLanguages")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "mangaList" })
     */
    private $manga;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userMangaLanguages")
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

    /**
     * @return Manga
     */
    public function getManga()
    {
        return $this->manga;
    }

    /**
     * @param mixed $manga
     * @return UserMangaLanguage
     */
    public function setManga($manga): self
    {
        $this->manga = $manga;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return UserMangaLanguage
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return Chapter
     */
    public function getLastChapter(): Chapter
    {
        return $this->lastChapter;
    }

    /**
     * @param Chapter $lastChapter
     * @return UserMangaLanguage
     */
    public function setLastChapter(Chapter $lastChapter): self
    {
        $this->lastChapter = $lastChapter;
        return $this;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * @param int $lastPage
     * @return UserMangaLanguage
     */
    public function setLastPage(int $lastPage): self
    {
        $this->lastPage = $lastPage;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->favorite;
    }

    /**
     * @param bool $favorite
     * @return UserManga
     */
    public function setFavorite(bool $favorite): UserManga
    {
        $this->favorite = $favorite;
        return $this;
    }
}
