<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ComicIssueReadRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ComicIssueReadRepository::class)
 * @ApiResource
 */
class ComicIssueRead
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $readDate;

    /**
     * @ORM\ManyToOne(targetEntity=ComicIssue::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $comicIssue;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReadDate(): ?DateTimeInterface
    {
        return $this->readDate;
    }

    public function setReadDate(?DateTimeInterface $readDate): self
    {
        $this->readDate = $readDate;

        return $this;
    }

    public function getComicIssue(): ?ComicIssue
    {
        return $this->comicIssue;
    }

    public function setComicIssue(?ComicIssue $comicIssue): self
    {
        $this->comicIssue = $comicIssue;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
