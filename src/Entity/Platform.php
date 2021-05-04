<?php


namespace App\Entity;

use App\Utils\PlatformUtil;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 */
class Platform
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "platformData" })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "platformData" })
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     * @Serializer\Groups({ "platformData" })
     */
    private $language = PlatformUtil::LANGUAGE_EN;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $baseUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mangaPath;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={'default': true})
     */
    private $active;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return Platform
     */
    public function setBaseUrl(string $baseUrl): Platform
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getMangaPath(): ?string
    {
        return $this->mangaPath;
    }

    /**
     * @param string $mangaPath
     * @return Platform
     */
    public function setMangaPath(string $mangaPath): Platform
    {
        $this->mangaPath = $mangaPath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Platform
     */
    public function setActive(bool $active): Platform
    {
        $this->active = $active;
        return $this;
    }
}
