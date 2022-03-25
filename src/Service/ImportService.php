<?php

namespace App\Service;

use App\Entity\Comic;
use App\Entity\ComicLanguage;
use App\MangaPlatform\PlatformNode;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ImportService
{
    /** @var Client $comicClient */
    private $comicClient;

    /** @var Client $comicIssueClient */
    private $comicIssueClient;

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var LoggerInterface $logger */
    private $logger;

    private const COMIC_CLIENT = 'comicClient';

    private const COMIC_ISSUE_CLIENT = 'comicIssueClient';

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger
    )
    {
        $this->em = $em;
        $this->logger = $logger;

        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox",
            '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36', // Avoir obligatoirement un user agent !!!
        ];

        $options = [
            'port' => mt_rand(9500, 9600),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
        ];
        $this->comicClient = Client::createChromeClient(null, $args, $options);
        $this->comicIssueClient = Client::createChromeClient(null, $args, $options);
    }

    public function importComic(
        string $slug,
        string $language = PlatformUtil::LANGUAGE_EN
    ): ?Comic
    {
        /** @var ComicLanguage|null $comicLanguage */
        $comicLanguage = $this->em->getRepository(ComicLanguage::class)->findBySlugAndLanguage($slug, $language);

        if ($comicLanguage === null) {
            return null;
        }

        $chapters = [];
        foreach ($comicLanguage->getComicPlatforms() as $platformEntity) {
            $this->openUrl(self::COMIC_CLIENT, $platformEntity->getUrl());
            $platform = PlatformUtil::getPlatform($platformEntity->getPlatform());

            if ($platform === null) {
                return null;
            }

            $title = $this->findNode(self::COMIC_CLIENT, $platform->getTitleNode());
            $this->logger->info('Title: ' . $title);
        }

        return $comicLanguage->getComic();
    }

    private function openUrl(string $client, string $url): void
    {
        /** @var Client $client */
        $client = $this->$client;

        if ($client instanceof Client && $client->getCurrentURL() !== $url) {
            $this->logger->info("[URL] opening $url");
            $client->request('GET', $url);
            $this->logger->info("[URL] $url opened.");
        }
    }

    private function findNode(string $client, PlatformNode $platformNode, array $callbackParameters = [])
    {
        $this->logger->info("[$client]: " . $platformNode->getName());
        if (!empty($selector = $platformNode->getSelector())) {
            /** @var Crawler $crawler */
            $crawler = $this->$client->getCrawler();

            $node = $crawler->filter($selector);

            if ($platformNode->hasCallback()) {
                return $platformNode->executeCallback($node, $callbackParameters);
            }

            if ($platformNode->isText()) {
                return $node->getText();
            }

            if (!empty($attribute = $platformNode->getAttribute())) {
                return $node->getAttribute($attribute);
            }

            return $node;
        }

        if ($platformNode->hasScript()) {
            return $platformNode->executeScript($this->$client, $callbackParameters);
        }

        return null;
    }
}