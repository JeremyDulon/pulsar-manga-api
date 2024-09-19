<?php


namespace App\Entity;

use App\Entity\Macro\Timestamps;
use App\Entity\Macro\Trust;
use App\Utils\PlatformUtil;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity]
class Platform
{
    use Timestamps;
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
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $baseUrl = '';

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $status = self::STATUS_ENABLED;


    public function __toString()
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): Platform
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function isStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): Platform
    {
        $this->status = $status;
        return $this;
    }
}
