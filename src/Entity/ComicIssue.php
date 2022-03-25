<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\ComicIssueRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ComicIssueRepository::class)
 */
class ComicIssue
{
    use Timestamps;

    public const TYPE_VOLUME = 100;
    public const TYPE_CHAPTER = 200;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "comicList", "comicIssue" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "comicList", "comicIssue" })
     */
    private $title;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false)
     * @Serializer\Groups({ "comicList", "comicIssue" })
     */
    private $number;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     * @Serializer\Groups({ "comicList", "comicIssue" })
     */
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({ "comicList", "comicIssue" })
     */
    private $date;

    /**
     * @var ComicLanguage
     *
     * @ORM\ManyToOne(targetEntity=ComicLanguage::class, inversedBy="comicIssues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comicLanguage;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=ComicPage::class, mappedBy="comicIssue", orphanRemoval=true, cascade={"remove"})
     * @Serializer\Groups({ "comicIssue" })
     */
    private $comicPages;

    /**
     * @var int
     * @Serializer\Groups({ "chapter" })
     */
    private $nextComicIssueId;

    /**
     * @var int
     * @Serializer\Groups({ "chapter" })
     */
    private $previousComicIssueId;

    public function __construct()
    {
        $this->comicPages = new ArrayCollection();
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

    public function getNumber(): ?float
    {
        return $this->number;
    }

    public function setNumber(float $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return ComicLanguage
     */
    public function getComicLanguage(): ComicLanguage
    {
        return $this->comicLanguage;
    }

    /**
     * @param ComicLanguage|null $comicLanguage
     * @return $this
     */
    public function setComicLanguage(?ComicLanguage $comicLanguage): self
    {
        $this->comicLanguage = $comicLanguage;

        return $this;
    }

    /**
     * @return Collection|ComicPage[]
     */
    public function getComicPages(): Collection
    {
        return $this->comicPages;
    }

    /**
     * @param ComicPage $chapterPage
     * @return self
     */
    public function addComicPage(ComicPage $chapterPage): self
    {
        if (!$this->comicPages->contains($chapterPage)) {
            $this->comicPages[] = $chapterPage;
            $chapterPage->setComicIssue($this);
        }

        return $this;
    }

    /**
     * @param ComicPage $chapterPage
     * @return self
     */
    public function removeComicPage(ComicPage $chapterPage): self
    {
        if ($this->comicPages->contains($chapterPage)) {
            $this->comicPages->removeElement($chapterPage);
            // set the owning side to null (unless already changed)
            if ($chapterPage->getComicIssue() === $this) {
                $chapterPage->setComicIssue(null);
            }
        }

        return $this;
    }

    /**
     * @return self
     */
    public function removeAllComicPages(): self
    {
        foreach ($this->comicPages as $chapterPage) {
            $this->removeComicPage($chapterPage);
        }

        return $this;
    }

    /**
     * @param int $id
     * @return ComicIssue
     */
    public function setNextComicIssueId(int $id): self
    {
        $this->nextComicIssueId = $id;
        return $this;
    }

    /**
     * @param int $id
     * @return ComicIssue
     */
    public function setPreviousComicIssueId(int $id): self
    {
        $this->nextComicIssueId = $id;
        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({ "chapter" })
     * @Serializer\SerializedName("comic_id")
     * @Serializer\Expose
     */
    public function getComicId(): int
    {
        return $this->getComicLanguage()->getId();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({ "chapter" })
     * @Serializer\SerializedName("comic_slug")
     * @Serializer\Expose
     */
    public function getComicSlug(): string
    {
        return $this->getComicLanguage()->getComic()->getSlug();
    }
}
