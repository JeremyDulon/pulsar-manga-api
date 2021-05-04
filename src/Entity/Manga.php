<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\MangaRepository::class)
 */
class Manga
{
    use Timestamps;

    public const STATUS_ONGOING = 100;
    public const STATUS_ENDED = 200;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Groups({ "mangaList" })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "mangaList", "mangaSlug" })
     */
    private $slug;


    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $lastUpdated;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @Serializer\Groups({ "mangaList" })
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "mangaList" })
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "platformData" })
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=MangaLanguage::class, mappedBy="manga", orphanRemoval=true)
     * @Serializer\Groups({ "mangaData" })
     */
    private $mangaLanguages;

    /**
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="manga", orphanRemoval=true)
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $chapters;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     * @Serializer\Groups({ "platformData" })
     */
    private $autoUpdate;

    /**
     * @ORM\OneToMany(targetEntity=UserMangaLanguage::class, mappedBy="manga", cascade={"remove"}, orphanRemoval=true)
     */
    private $userMangaLanguages;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->platforms = new ArrayCollection();
        $this->userMangaLanguages = new ArrayCollection();

    }

    public function __toString() {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAltTitles(): ?array
    {
        return $this->altTitles;
    }

    public function setAltTitles(array $altTitles): self
    {
        $this->altTitles = $altTitles;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(File $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|MangaPlatform[]
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function addPlatform(MangaPlatform $platform): self
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms[] = $platform;
            $platform->setManga($this);
        }

        return $this;
    }

    public function removePlatform(MangaPlatform $platform): self
    {
        if ($this->platforms->contains($platform)) {
            $this->platforms->removeElement($platform);
            // set the owning side to null (unless already changed)
            if ($platform->getManga() === $this) {
                $platform->setManga(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Chapter[]
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): self
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters[] = $chapter;
            $chapter->setManga($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->contains($chapter)) {
            $this->chapters->removeElement($chapter);
            // set the owning side to null (unless already changed)
            if ($chapter->getManga() === $this) {
                $chapter->setManga(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

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
     * @return Manga
     */
    public function setAutoUpdate(bool $autoUpdate): Manga
    {
        $this->autoUpdate = $autoUpdate;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({ "latest_chapter" })
     * @Serializer\SerializedName("latest_chapter")
     * @Serializer\Expose
     */
    public function getLatestChapter(): Chapter
    {
        return $this->getChapters()->last();
    }
}
