<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Chapter;
use App\Entity\Manga;
use App\MangaPlatform\AbstractPlatform;
use App\Utils\PlatformUtil;
use DateTime;
use Symfony\Component\Panther\DomCrawler\Crawler;

class MangaFastPlatform extends AbstractPlatform
{
    protected $name = 'MangaFast';

    protected $baseUrl = 'https://mangafast.net';

    protected $mangaPath = '/read/' . self::MANGA_SLUG;

    public function __construct() {
        parent::__construct();

        $this->setTitleNode();
        $this->setStatusNode();
        $this->setAltTitlesNode();
        $this->setMangaImageNode();
        $this->setAuthorNode();
        $this->setDescriptionNode();
        $this->setChapterDataNode();
        $this->setChapterPagesNode();
    }

    public function setMangaRegex()
    {
        $mangaRegex = $this->getMangaRegex();

        $mangaRegex->setRegex('/\/read\/((?:[a-z]*-?)*)/')
            ->setMangaPosition(1);
    }

    public function setTitleNode() {
        $titleNode = $this->getTitleNode();

        $titleNode->setSelector('.inftable tr td b');
        $titleNode->setText(true);
    }

    public function setStatusNode() {
        $statusNode = $this->getStatusNode();

        $statusNode->setSelector('.inftable tr:nth-child(6) td:nth-child(2)');
        $statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
        });
    }

    public function setAltTitlesNode() {
        $altTitlesNode = $this->getAltTitlesNode();

        $altTitlesNode->setSelector('.inftable tr:nth-child(2) td:nth-child(2)');
        $altTitlesNode->setCallback(function (Crawler $el) {
            return array_map(function ($v) {
                return trim($v);
            }, explode(';', $el->getText()));
        });
    }

    public function setMangaImageNode() {
        $mangaImageNode = $this->getMangaImageNode();

        $mangaImageNode->setSelector('#Thumbnail');
        $mangaImageNode->setAttribute('src');
    }

    public function setAuthorNode() {
        $authorNode = $this->getAuthorNode();

        $authorNode->setSelector('.inftable tr:nth-child(4) td:nth-child(2)');
        $authorNode->setText(true);
    }

    public function setDescriptionNode() {
        $descriptionNode = $this->getDescriptionNode();

        $descriptionNode->setSelector('#article-title p[itemprop=description]');
        $descriptionNode->setText(true);
    }

    public function setChapterDataNode() {
        $chapterDataNode = $this->getChapterDataNode();

        $chapterDataNode->setSelector('.chapter-link-w');
        $chapterDataNode->setCallback(function (Crawler $el, $parameters) {
            $chaptersArray = $el->children('.chapter-link')->reduce(function (Crawler $node) {
                $classes = $node->filter('.chapter-w')->attr('class');
                return strpos($classes, 'spoiler') === false;
            })->each(function (Crawler $ch) {
                $url = $ch->getAttribute('href');
                $title = $ch->filter('.chapter-w .left')->getText();
                $number = str_replace('Chapter ', '', $title);
                $date = DateTime::createFromFormat('Y-m-d', trim($ch->filter('.chapter-w .right')->getText()));
                return [
                    'title' => trim($title),
                    'number' => (int) $number,
                    'url' => $url,
                    'date' => $date->setTime(0, 0)
                ];
            });
            $offset = $parameters['offset'];
            $chapterNumber = $parameters['chapterNumber'];
            $numberCb = function ($ch) {
                return $ch['number'];
            };

            usort($chaptersArray, function ($chA, $chB) {
                return $chA['number'] < $chB['number'] ? -1 : 1;
            });

            $lastChapterNumber = (int) $numberCb(end($chaptersArray));

            $chaptersArray = PlatformUtil::filterChapters($chaptersArray, $numberCb, $lastChapterNumber, $offset, $chapterNumber);
            return $chaptersArray;
        });
    }

    public function setChapterPagesNode() {
        $chapterPagesNode = $this->getChapterPagesNode();

        $chapterPagesNode->setSelector('.content-comic img');
        $chapterPagesNode->setCallback(function (Crawler $el, $parameters) {
            /** @var Chapter|null $chapter */
            $chapter = $parameters['chapter'] ?? null;
            $val = [];
            if ($chapter) {
                $pageNumber = 0;
                $val = $el->each(function (Crawler $ch) use (&$pageNumber) {
                    $pageNumber++;
                    $url = $ch->getAttribute('data-src') ?? $ch->getAttribute('src');
                    return [
                       'number' => $pageNumber,
                       'url' => $url
                   ];
                });
            }
            return $val;
        });
    }
}
