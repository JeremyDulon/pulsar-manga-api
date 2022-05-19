<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Macro\Timestamps;
use App\Repository\ComicPageRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=ComicPageRepository::class)
 * @ApiResource
 */
class ComicPage
{
    use Timestamps;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ComicIssue::class, inversedBy="comicPages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comicIssue;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "comicIssue" })
     */
    private $number;

    /**
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({ "comicIssue" })
     */
    private $file;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }
}
