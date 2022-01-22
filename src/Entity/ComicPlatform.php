<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\ComicPlatformRepository::class)
 */
class ComicPlatform
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var int
     * @ORM\Column(name="weight", type="integer", nullable=false)
     */
    private $weight;

    /**
     * @var Platform
     * @ORM\ManyToOne(targetEntity=Platform::class)
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups()
     */
    private $platform;

    /**
     * @return int
     */
    public function getId(): int
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

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
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

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return ComicPlatform
     */
    public function setWeight(int $weight): ComicPlatform
    {
        $this->weight = $weight;
        return $this;
    }
}
