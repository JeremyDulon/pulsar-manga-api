<?php

namespace App\Entity;

use App\Entity\Macro\Trust;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
class ComicPlatform
{
    use Trust;

    const TRUST_FACTOR_POSITIVE = 1;
    const TRUST_FACTOR_NEGATIVE = -1;
    const TRUST_FACTOR_BAD = -3;

    const STATUS_ENABLED = 100;
    const STATUS_SUSPENDED = 200;
    const STATUS_DISABLED = 300;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $url;

    #[ORM\ManyToOne(targetEntity: Platform::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Platform $platform;

    #[ORM\ManyToOne(targetEntity: ComicLanguage::class, inversedBy: 'comicPlatforms')]
    private ComicLanguage $comicLanguage;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $status = self::STATUS_ENABLED;

    public function __toString()
    {
        return $this->platform->getName() . ' - ' . $this->url;
    }

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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ComicPlatform
     */
    public function setUrl(string $url): ComicPlatform
    {
        $this->url = $url;
        return $this;
    }

    /**4    * @return Platform
     */
    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    /**
     * @param Platform $platform
     * @return ComicPlatform
     */
    public function setPlatform(Platform $platform): ComicPlatform
    {
        $this->platform = $platform;
        return $this;
    }

    public function getComicLanguage(): ?ComicLanguage
    {
        return $this->comicLanguage;
    }

    public function setComicLanguage(?ComicLanguage $comicLanguage): self
    {
        $this->comicLanguage = $comicLanguage;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return ComicPlatform
     */
    public function setStatus(int $status): ComicPlatform
    {
        $this->status = $status;
        return $this;
    }
}

