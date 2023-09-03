<?php

namespace App\MangaPlatform;

use Exception;

abstract class AbstractPlatform implements PlatformInterface
{
    public const LANGUAGE_EN = 'EN';
    public const LANGUAGE_FR = 'FR';
    public const MANGA_SLUG = 'manga_slug';

    protected $language = self::LANGUAGE_EN;

    protected string $name;

    protected string $baseUrl;

    protected string $mangaPath;

    protected array $headers;

    protected string $domain;

    protected array $cookies;

    /** @var PlatformRegex $mangaRegex */
    protected PlatformRegex $mangaRegex;

    /** Utile ? */
    protected $chapterRegex;

    protected PlatformNode $titleNode;

    protected PlatformNode $altTitlesNode;

    protected PlatformNode $statusNode;

    protected PlatformNode $mangaImageNode;

    protected PlatformNode $authorNode;

    protected PlatformNode $viewsNode;

    protected PlatformNode $lastUpdatedNode;

    protected PlatformNode $descriptionNode;

    protected PlatformNode $comicIssuesDataNode;

    protected PlatformNode $comicPagesNode;

    /**
     * AbstractPlatform constructor.
     * @throws Exception
     */
    public function __construct() {
        $this->mangaRegex = new PlatformRegex();
        $this->chapterRegex = new PlatformRegex();

        $this->titleNode = new PlatformNode('title');
        $this->authorNode = new PlatformNode('author');
        $this->viewsNode = new PlatformNode('views');
        $this->altTitlesNode = new PlatformNode('altTitles');
        $this->statusNode = new PlatformNode('status');
        $this->mangaImageNode = new PlatformNode('image');
        $this->lastUpdatedNode = new PlatformNode('lastUpdated');
        $this->descriptionNode = new PlatformNode('description');
        $this->comicIssuesDataNode = new PlatformNode('comicIssuesData');
        $this->comicPagesNode = new PlatformNode('comicPages');

        $this->setMangaRegex();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @throws Exception
     */
    public function getHeaders()
    {
        throw new Exception('Not implemented');
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
    public function getMainImageNode(): PlatformNode
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
    public function getComicIssuesDataNode(): PlatformNode
    {
        return $this->comicIssuesDataNode;
    }

    /**
     * @return PlatformNode
     */
    public function getComicPagesNode(): PlatformNode
    {
        return $this->comicPagesNode;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }
}
