<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Chapter;
use App\Entity\Manga;
use App\MangaPlatform\AbstractPlatform;
use App\Utils\PlatformUtil;
use Closure;
use DateTime;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class FanFoxPlatform extends AbstractPlatform
{
    protected $name = 'FanFox';

    protected $baseUrl = 'https://fanfox.net';

    protected $mangaPath = '/manga/' . self::MANGA_SLUG;

    public function __construct() {
        parent::__construct();

        $this->setTitleNode();
        $this->setStatusNode();
        $this->setMangaImageNode();
        $this->setAuthorNode();
        $this->setDescriptionNode();
        $this->setChapterDataNode();
        $this->setChapterPagesNode();
    }

    public function setMangaRegex()
    {
        $mangaRegex = $this->getMangaRegex();

        $mangaRegex->setRegex('/\/manga\/((?:[a-z]*-?)*)/')
            ->setMangaPosition(1);
    }

    public function setTitleNode() {
        $titleNode = $this->getTitleNode();

        $titleNode->setSelector('.detail-info-right-title-font');
        $titleNode->setText(true);
    }

    public function setStatusNode() {
        $statusNode = $this->getStatusNode();

        $statusNode->setSelector('.detail-info-right-title-tip');
        $statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;
        });
    }

    // Checkme: Is url accessible with params ?
    public function setMangaImageNode() {
        $mangaImageNode = $this->getMangaImageNode();

        $mangaImageNode->setSelector('.detail-info-cover-img');
        $mangaImageNode->setAttribute('src');
    }

    public function setAuthorNode() {
        $authorNode = $this->getAuthorNode();

        $authorNode->setSelector('.detail-info-right-say a');
        $authorNode->setText(true);
    }

    public function setDescriptionNode() {
        $descriptionNode = $this->getDescriptionNode();

        $descriptionNode->setSelector('.fullcontent');
        $descriptionNode->setText(true);
    }

    public function setChapterDataNode() {
        $chapterDataNode = $this->getChapterDataNode();

        $chapterDataNode->setSelector('#chapterlist .detail-main-list');
        $chapterDataNode->setCallback(function (Crawler $el, $parameters) {
            $chaptersArray = $el->children('li')->reduce(function ($node) {
                $title = $node->filter('.title3')->getAttribute('innerText');
                preg_match('/Ch\.([0-9]*)/', $title, $matches);
                return !empty($matches) && ctype_digit($matches[1]) === true;
            })->each(function (Crawler $ch) {
                $a = $ch->filter('a');
                $url = $a->getAttribute('href');

                $title = $a->filter('.title3')->getAttribute('innerText');
                preg_match('/Ch\.([0-9]*)/', $title, $matches);
                $date = $a->filter('.title2')->getAttribute('innerText');
                return [
                    'title' => $title,
                    'number' => $matches[1],
                    'url' => $url,
                    'date' => new DateTime(trim($date))
                ];
            });
            // Todo: refacto
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

        $chapterPagesNode->setScript(function (Client $client, $parameters) {
            $key = $client->executeScript("
                var mkey = '';
                if ($('#dm5_key').length > 0) {
                    mkey = $('#dm5_key').val();
                }
                return mkey;
            ");
           $pages = $this->scriptAjaxChapterPages($client, 1);
           $pages = array_merge($pages, $this->scriptAjaxChapterPages($client, 3));

           $val = [];

           foreach (array_values(array_unique($pages)) as $i => $page) {
               $val[] = [
                   'number' => $i+1,
                   'url' => 'https:' . $page
               ];
           }
           return $val;
        });
    }

    private function waitAjaxChapter(): Closure
    {
        return static function ($driver): bool {
            return $driver->executeScript('return window.ajaxDone === true');
        };
    }

    private function scriptAjaxChapterPages($client, $page, $key = '""') {

        $sent = $client->executeScript("
            window.pages = [];
            window.ajaxDone = false;
            $.ajax({
                url: 'chapterfun.ashx',
                data: { cid: chapterid, page: $page, key: $key },
                type: 'GET',
                error: function (msg) {},
                success: function (msg) {
                    if (msg != '') {
                        var arr;
                        eval(msg);
                        window.pages = d;
                        window.ajaxDone = true;
                    }
                }
            });
            
            return 'ajaxSent';
        ");
        dump($sent);
        $client->getWebDriver()->wait()->until($this->waitAjaxChapter());
        $pages = $client->executeScript("return window.pages;");

        return $pages;
    }
}
