<?php

namespace App\Entity\Macro;

use Doctrine\ORM\Mapping as ORM;

trait Integrity
{

    /**
     * @var array $error
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $error;

    /**
     * @var int $integrity
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $integrity = 100;

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * @param array $error
     * @return self
     */
    public function setError(array $error): self
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return int
     */
    public function getIntegrity(): int
    {
        return $this->integrity;
    }

    /**
     * @param int $integrity
     * @return self
     */
    public function setIntegrity(int $integrity): self
    {
        $this->integrity = $integrity;
        return $this;
    }
}