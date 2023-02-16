<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Macro\Timestamps;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['list:Comic', 'read:File']],
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:Comic', 'read:File']]
        ]
    ]
)]
class Comic
{
    use Timestamps;

    public const STATUS_ONGOING = 100;
    public const STATUS_ENDED = 200;

    public const TYPE_MANGA = 100;
    public const TYPE_COMIC = 200;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private string $title = '';

    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Groups([ 'read:Comic' ])]
    private array $altTitles = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Gedmo\Slug(fields: ['title'])]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private string $slug;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private File $image;

    #[ORM\Column(type: 'integer')]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private int $type = self::TYPE_MANGA;

    #[ORM\Column(type: 'integer')]
    #[Groups([ 'list:Comic', 'read:Comic' ])]
    private int $status = self::STATUS_ONGOING;

    #[ORM\Column(type: 'string')]
    #[Groups([ 'read:Comic' ])]
    private string $author = '';

    #[ORM\OneToMany(mappedBy: 'comic', targetEntity: ComicLanguage::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups([ 'read:Comic' ])]
    private Collection $comicLanguages;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups([ 'read:Comic' ])]
    private DateTimeInterface $lastUpdated;

    public function __construct()
    {
        $this->comicLanguages = new ArrayCollection();
    }

    public function __toString() {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Comic
    {
        $this->title = $title;
        return $this;
    }

    public function getAltTitles(): array
    {
        return $this->altTitles;
    }

    public function setAltTitles(array $altTitles): Comic
    {
        $this->altTitles = $altTitles;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): Comic
    {
        $this->type = $type;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): Comic
    {
        $this->slug = $slug;
        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(File $image): Comic
    {
        $this->image = $image;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): Comic
    {
        $this->status = $status;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): Comic
    {
        $this->author = $author;
        return $this;
    }

    public function getComicLanguages()
    {
        return $this->comicLanguages;
    }

    public function addComicLanguage(ComicLanguage $comicLanguage): self
    {
        if (!$this->comicLanguages->contains($comicLanguage)) {
            $this->comicLanguages[] = $comicLanguage;
        }
        return $this;
    }

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
