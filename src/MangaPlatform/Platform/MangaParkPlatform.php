<?php

namespace App\MangaPlatform\Platform;

use App\Entity\Chapter;
use App\Entity\Manga;
use App\Utils\PlatformUtil;
use DateTime;
use Symfony\Component\Panther\DomCrawler\Crawler;

class MangaParkPlatform extends AbstractPlatform
{
    protected $name = 'MangaPark';

    protected $baseUrl = 'https://mangapark.net';

    protected $mangaPath = '/manga/' . self::MANGA_SLUG;

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

    public function setTitleNode() {
        $titleNode = $this->getTitleNode();

        $titleNode->setSelector('.cover img');
        $titleNode->setAttribute('title');
    }

    public function setStatusNode() {
        $statusNode = $this->getStatusNode();

        $statusNode->setSelector('.attr tr:nth-child(8');
        $statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
        });
    }

    public function setAltTitlesNode() {
        $altTitlesNode = $this->getAltTitlesNode();

        $altTitlesNode->setSelector('.attr tr:nth-child(4)');
        $altTitlesNode->setCallback(function (Crawler $el) {
            return array_map(function ($v) {
                return trim($v);
            }, explode(';', $el->getText()));
        });
    }

    public function setMangaImageNode() {
        $mangaImageNode = $this->getMangaImageNode();

        $mangaImageNode->setSelector('.cover img');
        $mangaImageNode->setAttribute('src');
    }

    public function setAuthorNode() {
        $authorNode = $this->getAuthorNode();

        $authorNode->setSelector('.attr tr:nth-child(5)');
        $authorNode->setText(true);
    }

    public function setDescriptionNode() {
        $descriptionNode = $this->getDescriptionNode();

        $descriptionNode->setSelector('.summary');
        $descriptionNode->setText(true);
    }

    public function setChapterDataNode() {
        $chapterDataNode = $this->getChapterDataNode();

        $chapterDataNode->setSelector('.book-list-1 #stream_1 .chapter');
        $chapterDataNode->setCallback(function (Crawler $el, $parameters) {
            $chaptersArray = $el->children('.item')->reduce(function (Crawler $node, $i) {
                $chapNumber = str_replace('ch.', '', $node->filter('a.ch')->getText());
                return ctype_digit($chapNumber) === true;
            })->each(function (Crawler $ch) {
                $chapterData = $ch->filter('a.ch');
                $number = str_replace('ch.', '', $chapterData->getText());
                $url = $ch->filter('.ext em a:nth-child(5)')->getAttribute('href');
                return [
                    'title' => 'Chapter ' . $number,
                    'number' => $number,
                    'url' => $url,
                    'date' => new DateTime($ch->filter('.time')->getText())
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

        $chapterPagesNode->setScript(function ($client, $parameters) {
            $chPages = $client->executeScript('return _load_pages');
            /** @var Chapter|null $chapter */
            $chapter = $parameters['chapter'] ?? null;
            $val = [];
            if ($chapter) {
                foreach ($chPages as $chPage) {
                    $val[] = [
                        'number' => $chPage['n'],
                        'url' => $chPage['u']
                    ];
                }
            }
            return $val;
        });
    }
}
