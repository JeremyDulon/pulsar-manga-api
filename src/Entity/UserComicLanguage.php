<?php

namespace App\Entity;

use App\Repository\UserComicRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=UserComicLanguageRepository::class)
 */
class UserComicLanguage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ComicLanguage::class, inversedBy="userComicLanguages")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "comicList" })
     */
    private $comic;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userComicLanguages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=ComicIssue::class)
     * @Serializer\Groups({ "comicList" })
     */
    private $lastComicIssue;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Groups({ "comicList" })
     */
    private $lastPage;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Serializer\Groups({ "comicList" })
     */
    private $favorite = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ComicLanguage
     */
    public function getComic(): ComicLanguage
    {
        return $this->comic;
    }

    /**
     * @param ComicLanguage $comic
     * @return UserComicLanguage
     */
    public function setComic(ComicLanguage $comic): self
    {
        $this->comic = $comic;
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
     * @param User $user
     * @return UserComicLanguage
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return ComicIssue
     */
    public function getLastChapter(): ComicIssue
    {
        return $this->lastComicIssue;
    }

    /**
     * @param ComicIssue $lastComicIssue
     * @return UserComicLanguage
     */
    public function setLastComicIssue(ComicIssue $lastComicIssue): self
    {
        $this->lastComicIssue = $lastComicIssue;
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
     * @return UserComicLanguage
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
     * @return UserComicLanguage
     */
    public function setFavorite(bool $favorite): UserComicLanguage
    {
        $this->favorite = $favorite;
        return $this;
    }
}
