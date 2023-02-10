<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\Macro\Timestamps;
use App\Repository\ComicIssueRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ComicIssueRepository::class)
 */

#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['list:ComicLanguage']],
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:ComicIssue', 'list:ComicPage', 'read:File']]
        ]
    ]
)]
class ComicIssue
{
    use Timestamps;

    public const TYPE_VOLUME = 100;
    public const TYPE_CHAPTER = 200;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({ "list:ComicIssue", "read:ComicIssue" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({ "list:ComicIssue", "read:ComicIssue" })
     */
    private $title;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=false)
     * @Groups({ "list:ComicIssue", "read:ComicIssue" })
     */
    private $number;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({ "comicList", "comicIssue" })
     */
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({ "list:ComicIssue", "read:ComicIssue" })
     */
    private $date;

    /**
     * @var ComicLanguage
     *
     * @ORM\ManyToOne(targetEntity=ComicLanguage::class, inversedBy="comicIssues")
     * @ORM\JoinColumn(nullable=false)
     * @ApiFilter(SearchFilter::class, properties={"comicLanguage": "exact"})
     */
    private $comicLanguage;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity=ComicPage::class, mappedBy="comicIssue", orphanRemoval=true, cascade={"remove"})
     * @Groups({ "list:ComicPage" })
     */
    private $comicPages;

    /**
     * @var int
     * @Groups({ "chapter" })
     */
    private $nextComicIssueId;

    /**
     * @var int
     * @Groups({ "chapter" })
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

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return ComicIssue
     */
    public function setType(int $type): self
    {
        $this->type = $type;
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
     */
    public function getComicId(): int
    {
        return $this->getComicLanguage()->getId();
    }

    /**
     */
    public function getComicSlug(): string
    {
        return $this->getComicLanguage()->getComic()->getSlug();
    }
}
