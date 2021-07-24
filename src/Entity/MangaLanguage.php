<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Repository\MangaPlatformRepository;
use App\Utils\PlatformUtil;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\MangaLanguageRepository::class)
 */
class MangaLanguage
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "mangaList", "addFavorite" })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $language = PlatformUtil::LANGUAGE_EN;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $description;

    /**
     * @var bool
     */
    private $autoUpdate;

    /**
     * @var Manga
     * @ORM\ManyToOne(targetEntity=Manga::class, inversedBy="languages")
     * @Serializer\Groups({ "mangaList" })
     */
    private $manga;

    /**
     * @var MangaPlatform
     * @ORM\OneToMany(targetEntity=MangaPlatform::class, mappedBy="mangaLanguage")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $platforms;

    /**
     * @var Chapter
     * @ORM\OneToMany(targetEntity=Chapter::class, mappedBy="mangaLanguage")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $chapters;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return MangaLanguage
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Manga|null
     */
    public function getManga(): ?Manga
    {
        return $this->manga;
    }

    /**
     * @param Manga|null $manga
     * @return $this
     */
    public function setManga(?Manga $manga): self
    {
        $this->manga = $manga;

        return $this;
    }

    /**
     * @return Collection|Chapter[]
     */
    public function getPlatforms(): Collection
    {
        return $this->platforms;
    }

    public function addPlatform(Platform $platform): self
    {
        if (!$this->platforms->contains($platform)) {
            $this->platforms[] = $platform;
            $platform->setManga($this);
        }

        return $this;
    }

    public function removeChapter(MangaLanguagePlatform $platform): self
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
}
