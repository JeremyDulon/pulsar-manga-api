<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Comic;
use App\Utils\PlatformUtil;
use Closure;
use DateTime;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class MangaSeePlatform extends AbstractPlatform
{
    protected string $name = 'MangaSee';

    protected string $mangaPath = '/manga/' . self::MANGA_SLUG;

    public function __construct() {
        parent::__construct();

        $this->domain = 'mangasee123.com';
        $this->baseUrl = 'https://mangasee123.com';
        $this->cookies = [];

        $this->setTitleNode();
        $this->setStatusNode();
        $this->setMainImageNode();
        $this->setAuthorNode();
        $this->setDescriptionNode();
        $this->setComicIssuesDataNode();
        $this->setComicPagesNode();
    }

    public function getHeaders(): array
    {
        return [
            'Referer: ' . $this->getBaseUrl()
        ];
    }

    public function setMangaRegex(): void
    {
        $this->mangaRegex->setRegex('/\/manga\/((?:[a-z]*-?)*)/')
            ->setMangaPosition(1);
    }

    public function setTitleNode(): void
    {
        $this->titleNode->setSelector('.list-group-item > h1');
        $this->titleNode->setText(true);

    }

    public function setStatusNode(): void
    {
        $this->statusNode->setXPathSelector('.//li[contains(@class, "list-group-item")]/span[text()="Status:"]/following-sibling::a[1]');
        $this->statusNode->setCallback(function (Crawler $el) {
            return str_contains(strtolower($el->getText()),'ongoing') ? Comic::STATUS_ONGOING : Comic::STATUS_ENDED;
        });
    }

    // Checkme: Is url accessible with params ?
    public function setMainImageNode(): void
    {
        $this->mangaImageNode->setSelector('.img-fluid.bottom-5');
        $this->mangaImageNode->setAttribute('src');
    }

    public function setAuthorNode(): void
    {
        $this->authorNode->setXPathSelector('.//li[contains(@class, "list-group-item")]/span[text()="Author(s):"]/following-sibling::a[1]');
        $this->authorNode->setText(true);
    }

    public function setDescriptionNode(): void
    {
        $this->descriptionNode->setXPathSelector('.//li[contains(@class, "list-group-item")]/span[text()="Description:"]/following-sibling::div');
        $this->descriptionNode->setText(true);
    }

    public function setComicIssuesDataNode(): void
    {
        $this->comicIssuesDataNode->setSelector('.ChapterLink');
        $this->comicIssuesDataNode->setScript(function (Client $client, $parameters) {
            $validIssues = $client->executeScript('
                let mainScope = angular.element(document.getElementsByClassName("MainContainer")).scope();
                let issues = mainScope.vm.Chapters;
                let validIssues = [];
                
                issues.forEach(issue => {
                    let chapterData = decodeChapterUrlAndNumber(issue.Chapter);
                    let date = issue.Date;
                    let url = location.origin + "/read-online/" + mainScope.vm.IndexName + chapterData.url;
                    let validIssue = {title: "", number: chapterData.number, url, date}
                    validIssues.push(validIssue)
                })
                
                function decodeChapterUrlAndNumber(e) {
                    let index = "";
                    let t = e.substring(0,1);
                    if (t != 1) {
                        index = "-index-" + t;
                    }
                    let n = parseInt(e.slice(1, -1))
                      , m = ""
                      , a = e[e.length - 1];
                    if (a != 0) {
                        m = "." + a
                    }
                    return {url: "-chapter-" + n + m + index + "-page-1.html", number: n};
                }
                
                return validIssues;
            ');

            foreach ($validIssues as &$issue) {
                $issue['date'] = new DateTime($issue['date']);
            }

            return PlatformUtil::filterIssues(
                $validIssues,
                $parameters
            );
        });
    }

    public function setComicPagesNode(): void
    {
        $this->comicPagesNode->setScript(function (Client $client, $parameters) {
            $pages = $client->executeScript('
                let mainScope = angular.element(document.getElementsByClassName("MainContainer")).scope();
                
                let pages = [];
                mainScope.vm.Pages.forEach((page) => {
                    let curPathName = mainScope.vm.CurPathName
                    let curChapterDirectory = mainScope.vm.CurChapter.Directory == "" ? "" : mainScope.vm.CurChapter.Directory + "/";
                    let chapterImage = mainScope.vm.ChapterImage(mainScope.vm.CurChapter.Chapter);
                    let pageImage = mainScope.vm.PageImage(page);
                    let pageUrl = `https://${curPathName}/manga/Dragon-Ball/${curChapterDirectory}${chapterImage}-${pageImage}.png`
                    pages.push(pageUrl);
                });
                
                return pages;
            ');

            $pagesArray = [];
            foreach ($pages as $i => $page) {
                $pagesArray[] = [
                    'url' => $page,
                    'number' => $i + 1
                ];
            }

            return $pagesArray;
        });
    }
}
