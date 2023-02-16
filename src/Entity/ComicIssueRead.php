<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ComicIssueReadRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource]
class ComicIssueRead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([ 'list:ComicIssue', 'read:ComicIssue' ])]
    private ?DateTimeInterface $readDate;

    #[ORM\ManyToOne(targetEntity: ComicIssue::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ComicIssue $comicIssue;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

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
