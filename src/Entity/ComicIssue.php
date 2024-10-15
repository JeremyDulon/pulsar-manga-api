<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\NextComicIssueController;
use App\Entity\Macro\Timestamps;
use App\Repository\ComicIssueRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComicIssueRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['list:ComicLanguage']],
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:ComicIssue', 'list:ComicPage', 'read:File']]
        ],
        'get_next' => [
            'method' => 'get',
            'path' => '/comic_issues/{id}/next',
            'controller' => NextComicIssueController::class,
            'normalization_context' => ['groups' => ['list:ComicIssue']]
        ]
    ]
)]
class ComicIssue
{
    use Timestamps;

    public const TYPE_VOLUME = 100;
    public const TYPE_CHAPTER = 200;

    public const QUALITY_GOOD = 100;
    public const QUALITY_POOR = 200;
    public const QUALITY_BAD = 300;
    public const QUALITY_ERROR = 400;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ 'list:ComicIssue', 'read:ComicIssue', 'list:ComicIssueFromUser' ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([ 'list:ComicIssue', 'read:ComicIssue', 'list:ComicIssueFromUser' ])]
    private string $title;

    #[ORM\Column(type: 'float', nullable: false)]
    #[Groups([ 'list:ComicIssue', 'read:ComicIssue', 'list:ComicIssueFromUser' ])]
    private float $number;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $type;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups([ 'list:ComicIssue', 'read:ComicIssue', 'list:ComicIssueFromUser' ])]
    private ?DateTimeInterface $date;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => self::QUALITY_GOOD])]
    private int $quality = self::QUALITY_GOOD;

    #[ORM\ManyToOne(targetEntity: ComicLanguage::class, inversedBy: 'comicIssues')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(SearchFilter::class, properties: ['comicLanguage' => 'exact'])]
    #[Groups([ 'read:ComicIssue' ])]
    private ComicLanguage $comicLanguage;

    #[ORM\ManyToOne(targetEntity: ComicPlatform::class, fetch: "EXTRA_LAZY")]
    private ComicPlatform $comicPlatform;

    #[ORM\OneToMany(mappedBy: 'comicIssue', targetEntity: ComicPage::class, cascade: ['remove'], orphanRemoval: true)]
    #[Groups([ 'list:ComicPage' ])]
    private Collection $comicPages;

    #[Groups([ 'read:comicIssueNext' ])]
    private int $nextComicIssueId;

    #[Groups([ 'read:comicIssueNext' ])]
    private int $previousComicIssueId;

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

    public function getType(): int
    {
        return $this->type;
    }

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

    public function getQuality(): int
    {
        return $this->quality;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getComicLanguage(): ComicLanguage
    {
        return $this->comicLanguage;
    }

    public function setComicLanguage(?ComicLanguage $comicLanguage): self
    {
        $this->comicLanguage = $comicLanguage;

        return $this;
    }

    public function getComicPages(): Collection
    {
        return $this->comicPages;
    }

    public function addComicPage(ComicPage $chapterPage): self
    {
        if (!$this->comicPages->contains($chapterPage)) {
            $this->comicPages[] = $chapterPage;
            $chapterPage->setComicIssue($this);
        }

        return $this;
    }

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

    public function removeAllComicPages(): self
    {
        foreach ($this->comicPages as $chapterPage) {
            $this->removeComicPage($chapterPage);
        }

        return $this;
    }

    public function setNextComicIssueId(int $id): self
    {
        $this->nextComicIssueId = $id;
        return $this;
    }

    public function setPreviousComicIssueId(int $id): self
    {
        $this->nextComicIssueId = $id;
        return $this;
    }

    public function getComicId(): int
    {
        return $this->getComicLanguage()->getId();
    }

    public function getComicSlug(): string
    {
        return $this->getComicLanguage()->getComic()->getSlug();
    }

    public function getComicPlatform(): ComicPlatform
    {
        return $this->comicPlatform;
    }

    public function setComicPlatform(ComicPlatform $comicPlatform): ComicIssue
    {
        $this->comicPlatform = $comicPlatform;
        return $this;
    }

    public function getPlatformAndLanguage(): string
    {
        return $this->comicPlatform->getPlatform()->getName() . ' - ' . $this->comicLanguage->getLanguage();
    }
}
