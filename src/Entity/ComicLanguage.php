<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Utils\PlatformUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\ComicLanguageRepository::class)
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(name="search_idx", columns={"language", "comic_id"})})
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
     * @ORM\Column(type="boolean", nullable=false)
     * @Serializer\Groups({ "comicList" })
     */
    private $autoUpdate = false;

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
    private $comicPlatforms;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=ComicIssue::class, mappedBy="comicLanguage")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $comicIssues;

    public function __toString(): string
    {
        return $this->comic->getTitle() . ' - ' . $this->language;
    }

    public function __construct()
    {
        $this->comicPlatforms = new ArrayCollection();
    }

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
    public function getComic(): ?Comic
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

    /**
     * @return Collection<int, ComicPlatform>
     */
    public function getComicPlatforms(): Collection
    {
        return $this->comicPlatforms;
    }

    public function addComicPlatform(ComicPlatform $comicPlatform): self
    {
        if (!$this->comicPlatforms->contains($comicPlatform)) {
            $this->comicPlatforms[] = $comicPlatform;
            $comicPlatform->setComicLanguage($this);
        }

        return $this;
    }

    public function removeComicPlatform(ComicPlatform $comicPlatform): self
    {
        if ($this->comicPlatforms->removeElement($comicPlatform)) {
            // set the owning side to null (unless already changed)
            if ($comicPlatform->getComicLanguage() === $this) {
                $comicPlatform->setComicLanguage(null);
            }
        }

        return $this;
    }

}
