<?php


namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Utils\PlatformUtil;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity
 */
class Platform
{
    use Timestamps;

    const STATUS_ENABLED = 100;
    const STATUS_SUSPENDED = 200;
    const STATUS_DISABLED = 300;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "platformData" })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Serializer\Groups({ "platformData" })
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $baseUrl;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $status;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

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
     * @return int
     */
    public function isStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Platform
     */
    public function setStatus(int $status): Platform
    {
        $this->status = $status;
        return $this;
    }
}
