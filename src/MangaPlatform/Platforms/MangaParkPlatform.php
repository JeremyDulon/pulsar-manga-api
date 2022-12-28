<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Chapter;
use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\Manga;
use App\Utils\PlatformUtil;
use DateTime;
use Symfony\Component\Panther\DomCrawler\Crawler;
use App\MangaPlatform\AbstractPlatform;

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
        $this->setMainImageNode();
        $this->setDescriptionNode();
        $this->setComicIssuesDataNode();
        $this->setComicPagesNode();
    }

    public function setMangaRegex()
    {
        $mangaRegex = $this->getMangaRegex();

        $mangaRegex->setRegex('/\/manga\/((?:[a-z]*-?)*)/')
            ->setMangaPosition(1);
    }

    public function setTitleNode() {
        $titleNode = $this->getTitleNode();

        $titleNode->setSelector('.item-title a');
        $titleNode->setText(true);
    }

    public function setStatusNode() {
        $statusNode = $this->getStatusNode();

        $statusNode->setSelector('.attr-main .attr-item:nth-child(6) span');
        $statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'Ongoing' ? Comic::STATUS_ONGOING : Comic::STATUS_ENDED;
        });
    }

    public function setAltTitlesNode() {
        $altTitlesNode = $this->getAltTitlesNode();

        $altTitlesNode->setSelector('.alias-set');
        $altTitlesNode->setCallback(function (Crawler $el) {
            return array_map(function ($v) {
                return trim($v);
            }, explode('―', $el->getText()));
        });
    }

    public function setMainImageNode() {
        $mangaImageNode = $this->getMainImageNode();

        $mangaImageNode->setSelector('.attr-cover img');
        $mangaImageNode->setAttribute('src');
    }

    /** Pas d'auteur sur la page */
    public function setAuthorNode() {
        $authorNode = $this->getAuthorNode();

        $authorNode->setSelector('.attr-main .attr-item:nth-child(6) span');
        $authorNode->setText(true);
    }

    public function setDescriptionNode() {
        $descriptionNode = $this->getDescriptionNode();

        $descriptionNode->setSelector('#limit-height-body-descr');
        $descriptionNode->setText(true);
    }

    public function setComicIssuesDataNode() {
        $chapterDataNode = $this->getComicIssuesDataNode();

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

            $chaptersArray = PlatformUtil::filterChapters(
                $chaptersArray,
                $parameters
            );

            return PlatformUtil::filterChapters($chaptersArray, $parameters);
        });
    }

    public function setComicPagesNode() {
        $chapterPagesNode = $this->getComicPagesNode();

        $chapterPagesNode->setScript(function ($client, $parameters) {
            $chPages = $client->executeScript('return _load_pages');
            /** @var ComicIssue|null $chapter */
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
