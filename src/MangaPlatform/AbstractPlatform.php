<?php

namespace App\MangaPlatform;

use Exception;

abstract class AbstractPlatform
{
    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';
    public const MANGA_SLUG = 'manga_slug';

    protected $name;

    protected $language = self::LANGUAGE_EN;

    protected $baseUrl;

    protected $mangaPath;

    /** @var PlatformRegex $mangaRegex */
    protected $mangaRegex;

    /** Utile ? */
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

    /** @var PlatformNode $lastUpdatedNode */
    protected $lastUpdatedNode;

    /** @var PlatformNode $descriptionNode */
    protected $descriptionNode;

    /** @var PlatformNode $chapterDataNode */
    protected $chapterDataNode;

    /** @var PlatformNode $chapterPagesNode */
    protected $chapterPagesNode;

    /**
     * AbstractPlatform constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->mangaRegex = new PlatformRegex();
        $this->chapterRegex = new PlatformRegex();

        $this->titleNode = new PlatformNode();
        $this->authorNode = new PlatformNode();
        $this->viewsNode = new PlatformNode();
        $this->altTitlesNode = new PlatformNode();
        $this->statusNode = new PlatformNode();
        $this->mangaImageNode = new PlatformNode();
        $this->lastUpdatedNode = new PlatformNode();
        $this->descriptionNode = new PlatformNode();
        $this->chapterDataNode = new PlatformNode();
        $this->chapterPagesNode = new PlatformNode();

        $this->setMangaRegex();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getMangaRegex(): PlatformRegex
    {
        return $this->mangaRegex;
    }

    /**
     * @throws Exception
     */
    public function setMangaRegex()
    {
        throw new Exception('Not implemented');
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
    public function getLastUpdatedNode(): PlatformNode
    {
        return $this->lastUpdatedNode;
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
