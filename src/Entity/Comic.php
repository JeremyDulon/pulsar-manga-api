<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Macro\Timestamps;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\ComicRepository::class)
 * @ApiResource
 */
class Comic
{
    use Timestamps;

    public const STATUS_ONGOING = 100;
    public const STATUS_ENDED = 200;

    public const TYPE_MANGA = 100;
    public const TYPE_COMIC = 200;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "comicList" })
     */
    private $title = '';

    /**
     * @var array
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $altTitles = [];

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(fields={"title"})
     * @Serializer\Groups({ "comicList", "comicSlug" })
     */
    private $slug;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @Serializer\Groups({ "comicList" })
     */
    private $image;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "comicList" })
     */
    private $type = self::TYPE_MANGA;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "comicList" })
     */
    private $status = self::STATUS_ONGOING;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "platformData" })
     */
    private $author = '';

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=ComicLanguage::class, mappedBy="comic", orphanRemoval=true)
     * @Serializer\Groups({ "comicData" })
     */
    private $comicLanguages;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({ "comicList" })
     */
    private $lastUpdated;

    public function __construct()
    {
        $this->comicLanguages = new ArrayCollection();
    }

    public function __toString() {
        return $this->getTitle();
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Comic
     */
    public function setTitle(string $title): Comic
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getAltTitles(): array
    {
        return $this->altTitles;
    }

    /**
     * @param array $altTitles
     * @return Comic
     */
    public function setAltTitles(array $altTitles): Comic
    {
        $this->altTitles = $altTitles;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Comic
     */
    public function setType(int $type): Comic
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Comic
     */
    public function setSlug(string $slug): Comic
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return File
     */
    public function getImage(): ?File
    {
        return $this->image;
    }

    /**
     * @param File $image
     * @return Comic
     */
    public function setImage(File $image): Comic
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Comic
     */
    public function setStatus(int $status): Comic
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return Comic
     */
    public function setAuthor(string $author): Comic
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getComicLanguages()
    {
        return $this->comicLanguages;
    }

    /**
     * @param ComicLanguage $comicLanguage
     * @return self
     */
    public function addComicLanguage(ComicLanguage $comicLanguage): self
    {
        if (!$this->comicLanguages->contains($comicLanguage)) {
            $this->comicLanguages[] = $comicLanguage;
        }
        return $this;
    }

    /**
     * @param ComicLanguage $comicLanguage
     * @return self
     */
    public function removeComicLanguage(ComicLanguage $comicLanguage): self
    {
        if ($this->comicLanguages->contains($comicLanguage)) {
            $this->comicLanguages->removeElement($comicLanguage);
            // set the owning side to null (unless already changed)
            if ($comicLanguage->getComic() === $this) {
                $comicLanguage->setComic(null);
            }
        }

        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getLastUpdated(): ?DateTimeInterface
    {
        return $this->lastUpdated;
    }

    /**
     * @param DateTimeInterface $lastUpdated
     * @return Comic
     */
    public function setLastUpdated(DateTimeInterface $lastUpdated): Comic
    {
        $this->lastUpdated = $lastUpdated;
        return $this;
    }
}
