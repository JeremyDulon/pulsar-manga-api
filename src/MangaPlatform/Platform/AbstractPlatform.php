<?php


namespace App\MangaPlatform\Platform;


abstract class AbstractPlatform
{
    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';
    public const MANGA_SLUG = 'manga_slug';

    protected $name;

    protected $language = self::LANGUAGE_EN;

    protected $baseUrl;

    protected $mangaPath;

    /** Utile ? */
    protected $mangaRegex;
    protected $chapterRegex;

    /** @var PlatformNode $titleNode */
    protected $titleNode;

    /** @var PlatformNode $altTitlesNode */
    protected $altTitlesNode;

    /** @var PlatformNode $statusNode */
    protected $statusNode;

    /** @var PlatformNode $mangaImageNode */
    protected $mangaImageNode;

    /** @var PlatformNode $authorNode */
    protected $authorNode;

    /** @var PlatformNode $viewsNode */
    protected $viewsNode;

    /** @var PlatformNode $lastUpdateNode */
    protected $lastUpdateNode;

    /** @var PlatformNode $descriptionNode */
    protected $descriptionNode;

    /** @var PlatformNode $chapterDataNode */
    protected $chapterDataNode;

    /** @var PlatformNode $chapterPagesNode */
    protected $chapterPagesNode;

    public function __construct() {
        $this->titleNode = new PlatformNode();
        $this->altTitlesNode = new PlatformNode();
        $this->statusNode = new PlatformNode();
        $this->mangaImageNode = new PlatformNode();
        $this->lastUpdateNode = new PlatformNode();
        $this->descriptionNode = new PlatformNode();
        $this->chapterDataNode = new PlatformNode();
        $this->chapterPagesNode = new PlatformNode();
    }

    /**
     * @return PlatformNode
     */
    public function getTitleNode(): PlatformNode
    {
        return $this->titleNode;
    }

    /**
     * @return PlatformNode
     */
    public function getAltTitlesNode(): PlatformNode
    {
        return $this->altTitlesNode;
    }

    /**
     * @return PlatformNode
     */
    public function getStatusNode(): PlatformNode
    {
        return $this->statusNode;
    }

    /**
     * @return PlatformNode
     */
    public function getMangaImageNode(): PlatformNode
    {
        return $this->mangaImageNode;
    }

    /**
     * @return PlatformNode
     */
    public function getAuthorNode(): PlatformNode
    {
        return $this->authorNode;
    }

    /**
     * @return PlatformNode
     */
    public function getViewsNode(): PlatformNode
    {
        return $this->viewsNode;
    }

    /**
     * @return PlatformNode
     */
    public function getLastUpdateNode(): PlatformNode
    {
        return $this->lastUpdateNode;
    }

    /**
     * @return PlatformNode
     */
    public function getDescriptionNode(): PlatformNode
    {
        return $this->descriptionNode;
    }

    /**
     * @return PlatformNode
     */
    public function getChapterDataNode(): PlatformNode
    {
        return $this->chapterDataNode;
    }

    /**
     * @return PlatformNode
     */
    public function getChapterPagesNode(): PlatformNode
    {
        return $this->chapterPagesNode;
    }
}
