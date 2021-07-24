<?php

namespace App\Entity;

use App\Entity\Macro\Timestamps;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=App\Repository\MangaRepository::class)
 */
class Manga
{
    use Timestamps;

    public const STATUS_ONGOING = 100;
    public const STATUS_ENDED = 200;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "mangaList" })
     */
    private $title;

    /**
     * TODO: V2 Utile ? A part pour la recherche je vois pas..
     * @var array
     * @ORM\Column(type="simple_array")
     */
    private $altTitles;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({ "mangaList", "mangaSlug" })
     */
    private $slug;

    /**
     * @var File
     *
     * @ORM\OneToOne(targetEntity=File::class, cascade={"persist", "remove"})
     * @Serializer\Groups({ "mangaList" })
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({ "mangaList" })
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({ "platformData" })
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=MangaLanguage::class, mappedBy="manga", orphanRemoval=true)
     * @Serializer\Groups({ "mangaData" })
     */
    private $languages;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Groups({ "mangaList" })
     */
    private $lastUpdated;

    public function __construct()
    {
        $this->languages = new ArrayCollection();

    }

    public function __toString() {
        return $this->getTitle();
    }


}
