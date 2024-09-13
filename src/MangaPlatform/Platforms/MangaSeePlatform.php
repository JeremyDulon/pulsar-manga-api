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
        $this->descriptionNode->setSelector('.fullcontent');
        $this->descriptionNode->setText(true);
    }

    public function setComicIssuesDataNode(): void
    {
        $this->comicIssuesDataNode->setScript(function (Client $client, $parameters) {
            $validIssues = $client->executeScript('
                let nodes = document.querySelectorAll("#chapterlist #list-2 .detail-main-list li")
                let validIssues = []
                
                nodes.forEach(node => {
                    let issueTitle = node.querySelector("p.title3").innerText
                    let matchData = issueTitle.match(/Ch\.([0-9]+(?:\.[0-9]+)?)/)
                    let issueNumber = parseFloat(matchData[1])
                    if (Number.isInteger(issueNumber) === true) {
                        let url = node.querySelector("a").href
                        let date = node.querySelector("p.title2").innerText
                        let validIssue = {title: issueTitle, number: issueNumber, url, date}
                        validIssues.push(validIssue)
                    }
                })
                
                return validIssues
            ');

            foreach ($validIssues as &$issue) {
                $issue['date'] = new DateTime(trim($issue['date']));
            }

            return PlatformUtil::filterIssues(
                $validIssues,
                $parameters
            );
        });
    }

    public function setComicPagesNode(): void
    {

    }
}
