<?php


namespace App\Entity;

use App\Entity\Macro\Timestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 */
class File
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "image" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "image" })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "image" })
     */
    private $path;

    /**
     * @var string
     * @Serializer\Groups({ "image" })
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "image" })
     */
    private $externalUrl;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
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
     * @return File
     */
    public function setUrl(string $url): File
    {
        $this->url = $url;
        return $this;
    }

    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    public function setExternalUrl(?string $externalUrl): self
    {
        $this->externalUrl = $externalUrl;

        return $this;
    }
}
