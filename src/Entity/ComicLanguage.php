<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\ComicPlatformRepository;
use App\Utils\PlatformUtil;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\ComicLanguageRepository::class)
 */
class ComicLanguage
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "comicList", "addFavorite" })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $language = PlatformUtil::LANGUAGE_EN;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({ "comicList" })
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="text", nullable=false)
     * @Serializer\Groups({ "comicList" })
     */
    private $autoUpdate;

    /**
     * @var Comic
     *
     * @ORM\ManyToOne(targetEntity=Comic::class, inversedBy="comicLanguages")
     * @Serializer\Groups({ "comicList" })
     */
    private $comic;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=ComicPlatform::class, mappedBy="comicLanguage")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $platforms;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=ComicIssue::class, mappedBy="comicLanguage")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $comicIssues;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ComicLanguage
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return ComicLanguage
     */
    public function setLanguage(string $language): ComicLanguage
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }

    /**
     * @param bool $autoUpdate
     * @return ComicLanguage
     */
    public function setAutoUpdate(bool $autoUpdate): ComicLanguage
    {
        $this->autoUpdate = $autoUpdate;
        return $this;
    }

    /**
     * @return Comic
     */
    public function getComic(): Comic
    {
        return $this->comic;
    }

    /**
     * @param Comic $comic
     * @return ComicLanguage
     */
    public function setComic(Comic $comic): ComicLanguage
    {
        $this->comic = $comic;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    /**
     * @param Platform $platform
     * @return self
     */
    public function addPlatform(Platform $platform): self
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms[] = $platform;
        }

        return $this;
    }

    /**
     * @param Platform $platform
     * @return self
     */
    public function removeComicPage(Platform $platform): self
    {
        if ($this->platforms->contains($platform)) {
            $this->platforms->removeElement($platform);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getComicIssues(): Collection
    {
        return $this->comicIssues;
    }

    /**
     * @param ComicIssue $comicIssue
     * @return self
     */
    public function addComicIssue(ComicIssue $comicIssue): self
    {
        if (!$this->comicIssues->contains($comicIssue)) {
            $this->comicIssues[] = $comicIssue;
        }
        return $this;
    }

    /**
     * @param ComicIssue $comicIssue
     * @return self
     */
    public function removeComicIssue(ComicIssue $comicIssue): self
    {
        if ($this->comicIssues->contains($comicIssue)) {
            $this->comicIssues->removeElement($comicIssue);
            // set the owning side to null (unless already changed)
            if ($comicIssue->getComic() === $this) {
                $comicIssue->setComic(null);
            }
        }

        return $this;
    }

}
