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

    public function setTitleNode() {
        $titleNode = $this->getTitleNode();

        $titleNode->setSelector('.inftable tr td b');
        $titleNode->setText(true);
    }

    public function setStatusNode() {
        $statusNode = $this->getStatusNode();

        $statusNode->setSelector('.inftable tr:nth-child(5) td:nth-child(1)');
        $statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
        });
    }

    public function setAltTitlesNode() {
        $altTitlesNode = $this->getAltTitlesNode();

        $altTitlesNode->setSelector('.inftable tr:nth-child(1) td:nth-child(1)');
        $altTitlesNode->setCallback(function (Crawler $el) {
            return [$el->getText()];
        });
    }

    public function setMangaImageNode() {
        $mangaImageNode = $this->getMangaImageNode();

        $mangaImageNode->setSelector('#Thumbnail');
        $mangaImageNode->setAttribute('data-src');
    }

    public function setAuthorNode() {
        $authorNode = $this->getAuthorNode();

        $authorNode->setSelector('.inftable tr:nth-child(3) td:nth-child(1)');
        $authorNode->setText(true);
    }

    public function setDescriptionNode() {
        $descriptionNode = $this->getDescriptionNode();

        $descriptionNode->setSelector('.sc p[itemprop=description]');
        $descriptionNode->setText(true);
    }

    public function setChapterDataNode() {
        $chapterDataNode = $this->getChapterDataNode();

        $chapterDataNode->setSelector('.lsch tr[itemprop=hasPart]');
        $chapterDataNode->setCallback(function (Crawler $el, $parameters) {
            $chaptersArray = array_filter($el->toArray(), function ($node) {
                $a = $node->filter('.jds a');
                $date = $node->filter('.tgs');
                return ctype_digit($a->find('span[itemprop=issueNumber]')->text) === true && trim($date->text) !== 'Scheduled';
            });
            $offset = $parameters['offset'];
            $chapterNumber = $parameters['chapterNumber'];
            $val = [];
            $numberCb = function ($node) {
                return $node->filter('.jds a span')->text;
            };

            usort($chaptersArray, function ($a, $b) {
                return $a->filter('span[itemprop=issueNumber]')->text < $b->filter('span[itemprop=issueNumber]')->text
                    ? -1
                    : 1;
            });

            $lastChapterNumber = (int) $numberCb(end($chaptersArray));

            $chaptersArray = PlatformUtil::filterChapters($chaptersArray, $numberCb, $lastChapterNumber, $offset, $chapterNumber);
            foreach ($chaptersArray as $chapterNode) {
                $a = $chapterNode->filter('.jds a');
                $number = $a->filter('span[itemprop=issueNumber]')->text;
                $url = $a->getAttribute('href');
                $val[] = [
                    'title' => trim($a->innerText()),
                    'number' => $number,
                    'url' => $url,
                    'date' => DateTime::createFromFormat('Y-m-d', trim($chapterNode->filter('.tgs')->text))->setTime(0, 0)
                ];
            }
            return $val;
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
