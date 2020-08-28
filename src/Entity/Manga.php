<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\MangaRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=MangaRepository::class)
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
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({ "mangaList" })
     */
    private $title;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @JMS\Groups({ "mangaList" })
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Groups({ "mangaList" })
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({ "mangaList" })
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({ "mangaList" })
     */
    private $lastUpdated;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({ "mangaList" })
     */
    private $views;

    /**
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="manga", orphanRemoval=true)
     * @JMS\Groups({ "mangaList" })
     */
    private $chapters;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getLastUpdated(): ?DateTimeInterface
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?DateTimeInterface $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getViews(): ?string
    {
        return $this->views;
    }

    public function setViews(?string $views): self
    {
        $this->views = $views;

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

    /**
     * @return File
     */
    public function getImage(): File
    {
        return $this->image;
    }

    /**
     * @param File $image
     * @return Manga
     */
    public function setImage(File $image): Manga
    {
        $this->image = $image;
        return $this;
    }
}
