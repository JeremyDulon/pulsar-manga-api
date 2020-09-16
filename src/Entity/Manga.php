<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
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
     * @JMS\Groups({ "mangaList" })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({ "mangaList" })
     */
    private $slug;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $altTitles;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @JMS\Groups({ "mangaList" })
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Groups({ "mangaList" })
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=MangaPlatform::class, mappedBy="manga", orphanRemoval=true)
     * @JMS\Groups({ })
     */
    private $platforms;

    public function __construct()
    {
        $this->platforms = new ArrayCollection();
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


}
