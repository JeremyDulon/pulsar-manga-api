<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\MangaLanguagePlatformRepository::class)
 */
class MangaLanguagePlatform
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MangaLanguage
     * @ORM\ManyToOne(targetEntity=MangaLanguage::class, inversedBy="platforms")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups()
     */
    private $mangaLanguage;

    /**
     * @var Platform
     * @ORM\ManyToOne(targetEntity=Platform::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups()
     */
    private $platform;

    /**
     * @var int
     * @ORM\Column(name="weight", type="integer", nullable=false)
     */
    private $weight;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return MangaLanguage
     */
    public function getMangaLanguage(): MangaLanguage
    {
        return $this->mangaLanguage;
    }

    /**
     * @param MangaLanguage $mangaLanguage
     * @return MangaLanguagePlatform
     */
    public function setMangaLanguage(MangaLanguage $mangaLanguage): MangaLanguagePlatform
    {
        $this->mangaLanguage = $mangaLanguage;
        return $this;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @param Platform $platform
     * @return MangaLanguagePlatform
     */
    public function setPlatform(Platform $platform): MangaLanguagePlatform
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return MangaLanguagePlatform
     */
    public function setWeight(int $weight): MangaLanguagePlatform
    {
        $this->weight = $weight;
        return $this;
    }
}
