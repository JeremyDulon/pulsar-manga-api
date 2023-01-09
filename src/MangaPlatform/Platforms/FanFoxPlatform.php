<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Chapter;
use App\Entity\Comic;
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

    protected $baseUrl = 'http://fanfox.net';

    protected $mangaPath = '/manga/' . self::MANGA_SLUG;

    public function __construct() {
        parent::__construct();

        $this->setTitleNode();
        $this->setStatusNode();
        $this->setMainImageNode();
        $this->setAuthorNode();
        $this->setDescriptionNode();
        $this->setComicIssuesDataNode();
        $this->setComicPagesNode();
    }

    public function getHeaders()
    {
        return [
            'Referer: ' . $this->getBaseUrl()
        ];
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
            return $el->getText() === 'Ongoing' ? Comic::STATUS_ONGOING : Comic::STATUS_ENDED;
        });
    }

    // Checkme: Is url accessible with params ?
    public function setMainImageNode() {
        $mangaImageNode = $this->getMainImageNode();

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

    public function setComicIssuesDataNode() {
        $chapterDataNode = $this->getComicIssuesDataNode();

        $chapterDataNode->setSelector('#chapterlist #list-2 .detail-main-list');
        $chapterDataNode->setCallback(function (Crawler $el, $parameters) {
            $chaptersArray = $el->children('li')->reduce(function (Crawler $node) {
                $title = $node->filterXpath('.//p[@class="title3"]')->getElement(0)->getDOMProperty('innerText');
                preg_match('/Ch\.([0-9]+(?:\.[0-9]+)?)/', $title, $matches);
                return !empty($matches) && ctype_digit($matches[1]) === true;
            })->each(function (Crawler $issue) {
                $a = $issue->filter('a');
                $url = $a->getAttribute('href');

                $title = $issue->filterXpath('.//p[@class="title3"]')->getElement(0)->getDOMProperty('innerText');
                preg_match('/Ch\.([0-9]*)/', $title, $matches);
                $date = $a->filterXpath('.//p[@class="title2"]')->getElement(0)->getDOMProperty('innerText');
                return [
                    'title' => $title,
                    'number' => $matches[1],
                    'url' => $url,
                    'date' => new DateTime(trim($date))
                ];
            });
            return PlatformUtil::filterChapters(
                $chaptersArray,
                $parameters
            );
        });
    }

    public function setComicPagesNode() {
        $chapterPagesNode = $this->getComicPagesNode();

        $chapterPagesNode->setScript(function (Client $client, $parameters) {
            $key = $client->executeScript("
                window.ajaxDone = [];
                var mkey = '';
                if ($('#dm5_key').length > 0) {
                    mkey = $('#dm5_key').val();
                }
                return mkey;
            ");

            $pagesCount = $client->executeScript("return imagecount");

            $pages = [];
            $currentPage = 1;
            while ($pagesCount > count($pages)) {
                $pagesToAdd = $this->scriptAjaxChapterPages($client, $currentPage);
                $currentPage++;

                foreach ($pagesToAdd as $page) {
                    if (!in_array($page, $pages)) {
                        $pages[] = $page;
                    }
                }
            }

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

    private function waitAjaxChapter(int $page): Closure
    {
        return static function ($driver) use ($page): bool {
            return $driver->executeScript("return window.ajaxDone[$page] === true");
        };
    }

    private function scriptAjaxChapterPages($client, $page, $key = '""') {

        $client->executeScript("
            window.pages = [];
            window.ajaxDone[$page] = false;
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
                        window.ajaxDone[$page] = true;
                    }
                }
            });
        ");
        $client->getWebDriver()->wait()->until($this->waitAjaxChapter($page));
        return $client->executeScript("return window.pages;");
    }
}
