<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\MangaPlatformRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=MangaPlatformRepository::class)
 */
class MangaPlatform
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $description;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $lastUpdated;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $viewsCount;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({ "platformData" })
     */
    private $sourceUrl;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Groups({ })
     */
    private $sourceSlug;

    /**
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="platforms")
     * @Serializer\Groups({ "mangaList" })
     */
    private $manga;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $platform;

    /**
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="manga", orphanRemoval=true)
     * @Serializer\Groups({ "chapterList" })
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

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?\DateTimeInterface $lastUpdated): self
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getViewsCount(): ?string
    {
        return $this->viewsCount;
    }

    public function setViewsCount(?string $viewsCount): self
    {
        $this->viewsCount = $viewsCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceUrl(): string
    {
        return $this->sourceUrl;
    }

    /**
     * @param string $sourceUrl
     * @return MangaPlatform
     */
    public function setSourceUrl(string $sourceUrl): MangaPlatform
    {
        $this->sourceUrl = $sourceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceSlug(): string
    {
        return $this->sourceSlug;
    }

    /**
     * @param string $sourceSlug
     * @return MangaPlatform
     */
    public function setSourceSlug(string $sourceSlug): MangaPlatform
    {
        $this->sourceSlug = $sourceSlug;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    public function setManga(?Manga $manga): self
    {
        $this->manga = $manga;

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }
}
