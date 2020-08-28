<?php


namespace App\Entity;

use App\Entity\Macro\Timestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

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
     * @JMS\Groups({ "image" })
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({ "image" })
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({ "image" })
     */
    private $path;

    /**
     * @var string
     * @JMS\Groups({ "image" })
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({ "image" })
     */
    private $externalUrl;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return File
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return File
     */
    public function setPath(string $path): self
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

    /**
     * @return string
     */
    public function getExternalUrl()
    {
        return $this->externalUrl;
    }

    /**
     * @param string $externalUrl
     * @return File
     */
    public function setExternalUrl(string $externalUrl)
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }
}
