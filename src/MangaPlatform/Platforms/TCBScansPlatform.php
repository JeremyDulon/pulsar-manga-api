<?php

namespace App\MangaPlatform\Platforms;

use App\Entity\Comic;
use App\Utils\PlatformUtil;
use Closure;
use DateTime;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class TCBScansPlatform extends AbstractPlatform
{
    protected string $name = 'TCBScans';

    protected string $mangaPath = '/manga/' . self::MANGA_SLUG;

    public function __construct() {
        parent::__construct();

        $this->domain = 'tcbscans-manga.com';
        $this->baseUrl = 'https://tcbscans-manga.com';
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
        $this->titleNode->setSelector('.post-title');
        $this->titleNode->setText(true);

    }

    public function setStatusNode(): void
    {
        $this->statusNode->setSelector('.post-status .post-content_item:nth-child(2) .summary-content');
        $this->statusNode->setCallback(function (Crawler $el) {
            return $el->getText() === 'OnGoing' ? Comic::STATUS_ONGOING : Comic::STATUS_ENDED;
        });
    }

    // Checkme: Is url accessible with params ?
    public function setMainImageNode(): void
    {
        $this->mangaImageNode->setSelector('.summary_image img');
        $this->mangaImageNode->setAttribute('src');
    }

    public function setAuthorNode(): void
    {
        $this->authorNode->setSelector('.artist-content a');
        $this->authorNode->setText(true);
    }

    public function setDescriptionNode(): void
    {
        $this->descriptionNode->setSelector('.description-summary .summary__content');
        $this->descriptionNode->setText(true);
    }

    public function setComicIssuesDataNode(): void
    {
        $this->comicIssuesDataNode->setSelector('.listing-chapters_wrap .main.version-chap');
        $this->comicIssuesDataNode->setMustWait(true);
        $this->comicIssuesDataNode->setCallback(function (Crawler $el, $parameters) {
           $issueArray = $el->children('.wp-manga-chapter')->each(function (Crawler $node) {
               $issueLinkNode = $node->filter('a');
               $issueTitle = $issueLinkNode->getElement(0)->getDOMProperty('innerText');
               $issueLink = $issueLinkNode->getAttribute('href');
               $dateNode = $node->filter('.chapter-release-date');
               $strDate = 'today';
               if ($dateNode->getElement(0) !== null) {
                   $strDate = $dateNode->getElement(0)->getDOMProperty('innerText');
               }
               return [
                   'title' => $issueTitle,
                   'number' => str_replace('Chapter ', '', $issueTitle),
                   'date' => new DateTime($strDate),
                   'url' => $issueLink
              ];
           });

           return PlatformUtil::filterIssues($issueArray, $parameters);
        });
    }

    public function setComicPagesNode(): void
    {
        $this->comicPagesNode->setSelector('#chapter-video-frame p');
        $this->comicPagesNode->setCallback(function(Crawler $el, $parameters) {
            $pageNumber = 1;
            return $el->children('img')->each(function (Crawler $node) use (&$pageNumber) {
                $url = $node->getAttribute('src');
                return [
                    'url' => $url,
                    'number' => $pageNumber++
                ];
            });
        });
    }
}
