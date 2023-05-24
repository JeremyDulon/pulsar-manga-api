<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Macro\Timestamps;
use App\Repository\ComicLanguageRepository;
use App\Utils\PlatformUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ComicLanguageRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'search_idx', columns: ['language', 'comic_id'])]
#[ApiResource(
    collectionOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['list:ComicLanguage', 'list:Comic', 'read:File']],
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:ComicLanguage', 'list:ComicIssue', 'list:Comic', 'read:File']]
        ]
    ]
)]
class ComicLanguage
{
    use Timestamps;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups([ 'read:ComicLanguage', 'list:ComicLanguage', 'read:ComicIssue' ])]
    private int $id;

    #[ORM\Column(type: 'string', length: 5, nullable: false)]
    #[Groups([ 'read:Comic', 'list:Comic', 'read:ComicLanguage', 'list:ComicLanguage' ])]
    private string $language = PlatformUtil::LANGUAGE_EN;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups([ 'read:ComicLanguage' ])]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $autoUpdate = false;

    #[ORM\ManyToOne(targetEntity: Comic::class, inversedBy: 'comicLanguages')]
    #[Groups([ 'list:ComicLanguage', 'read:ComicLanguage' ])]
    private Comic $comic;

    #[ORM\OneToMany(mappedBy: 'comicLanguage', targetEntity: ComicPlatform::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $comicPlatforms;

    #[ORM\OneToMany(mappedBy: 'comicLanguage', targetEntity: ComicIssue::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'list:ComicIssue' ])]
    private Collection $comicIssues;

    public function __toString(): string
    {
        if ($this->comic === null) {
            return 'New comic ' . ' - ' . $this->language;
        }

        return $this->comic->getTitle() . ' - ' . $this->language;
    }

    public function __construct()
    {
        $this->comicPlatforms = new ArrayCollection();
        $this->comicIssues = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): ComicLanguage
    {
        $this->language = $language;
        return $this;
    }

    public function isAutoUpdate(): bool
    {
        return $this->autoUpdate;
    }

    public function setAutoUpdate(bool $autoUpdate): ComicLanguage
    {
        $this->autoUpdate = $autoUpdate;
        return $this;
    }

    public function getComic(): ?Comic
    {
        return $this->comic;
    }

    public function setComic(?Comic $comic): ComicLanguage
    {
        $this->comic = $comic;
        return $this;
    }

    public function getComicIssues(): Collection
    {
        return $this->comicIssues;
    }

    public function addComicIssue(ComicIssue $comicIssue): self
    {
        if (!$this->comicIssues->contains($comicIssue)) {
            $this->comicIssues[] = $comicIssue;
        }
        return $this;
    }

    public function removeComicIssue(ComicIssue $comicIssue): self
    {
        if ($this->comicIssues->contains($comicIssue)) {
            $this->comicIssues->removeElement($comicIssue);
            // set the owning side to null (unless already changed)
            if ($comicIssue->getComicLanguage() === $this) {
                $comicIssue->setComicLanguage(null);
            }
        }

        return $this;
    }

    public function getComicPlatforms(): Collection
    {
        return $this->comicPlatforms->filter(function (ComicPlatform $comicPlatform) {
            return $comicPlatform->getStatus() === ComicPlatform::STATUS_ENABLED;
        });
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

//    #[]
    public function getLatestComicIssue(): ?ComicIssue
    {
        if ($this->comicIssues->isEmpty() === true) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->orderBy(['number' => 'DESC'])
            ->getMaxResults(1);
        return $this->comicIssues->matching($criteria)->first();
    }
}
