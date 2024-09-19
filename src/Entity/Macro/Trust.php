<?php

namespace App\Entity\Macro;

use Doctrine\ORM\Mapping as ORM;

trait Trust
{
    #[ORM\Column(type: 'integer', nullable: false)]
    protected int $trust = 100;
    /**
     * @return int
     */
    public function getTrust(): int
    {
        return $this->trust;
    }

    public function setTrust(int $trust): self
    {
        $this->trust = $trust;
        return $this;
    }

    public function updateTrust(int $factor): self
    {
        $this->trust = $this->trust + $factor;
        return $this;
    }
}
