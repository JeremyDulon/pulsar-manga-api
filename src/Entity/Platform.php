<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Platform
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    private $language;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $baseUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $mangaPath;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $chapterPath;

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
    public function getBaseUrl(): string
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
    public function getMangaPath(): string
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
     * @return string
     */
    public function getChapterPath(): string
    {
        return $this->chapterPath;
    }

    /**
     * @param string $chapterPath
     * @return Platform
     */
    public function setChapterPath(string $chapterPath): Platform
    {
        $this->chapterPath = $chapterPath;
        return $this;
    }
}
