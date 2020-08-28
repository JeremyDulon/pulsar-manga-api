<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\ChapterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass=ChapterRepository::class)
 */
class Chapter
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\Groups({ "mangaList", "chapter" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({ "mangaList", "chapter" })
     */
    private $title;

    /**
     * @ORM\Column(type="float")
     * @JMS\Groups({ "mangaList", "chapter" })
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({ "mangaList", "chapter" })
     */
    private $platform;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({ "mangaList", "chapter" })
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="chapters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $manga;

    /**
     * @ORM\OneToMany(targetEntity=ChapterPage::class, mappedBy="chapter", orphanRemoval=true)
     * @JMS\Groups({ "chapter" })
     */
    private $chapterPages;

    public function __construct()
    {
        $this->chapterPages = new ArrayCollection();
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

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    /**
     * @return Collection|ChapterPage[]
     */
    public function getChapterPages(): Collection
    {
        return $this->chapterPages;
    }

    public function addChapterPage(ChapterPage $chapterPage): self
    {
        if (!$this->chapterPages->contains($chapterPage)) {
            $this->chapterPages[] = $chapterPage;
            $chapterPage->setChapter($this);
        }

        return $this;
    }

    public function removeChapterPage(ChapterPage $chapterPage): self
    {
        if ($this->chapterPages->contains($chapterPage)) {
            $this->chapterPages->removeElement($chapterPage);
            // set the owning side to null (unless already changed)
            if ($chapterPage->getChapter() === $this) {
                $chapterPage->setChapter(null);
            }
        }

        return $this;
    }

    public function removeAllChapterPages() {
        foreach ($this->chapterPages as $chapterPage) {
            $this->removeChapterPage($chapterPage);
        }

        return $this;
    }
}
