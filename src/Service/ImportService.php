<?php

namespace App\Service;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPage;
use App\Entity\ComicPlatform;
use App\Entity\File;
use App\MangaPlatform\PlatformInterface;
use App\MangaPlatform\PlatformNode;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\ProcessManager\ChromeManager;

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

    /** @var ImageService $imageService */
    private $imageService;

    /** @var ParameterBagInterface $parameterBag */
    private $parameterBag;

    private const COMIC_CLIENT = 'comicClient';

    private const COMIC_ISSUE_CLIENT = 'comicIssueClient';

    /** @var array $comicIssues */
    private $comicIssues = [];

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        ImageService $imageService,
        ParameterBagInterface $parameterBag
    )
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->imageService = $imageService;
        $this->parameterBag = $parameterBag;

        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox",
            '--disable-dev-shm-usage',
            '--user-agent=' . $this->parameterBag->get('user_agent'), // Avoir obligatoirement un user agent !!!
        ];

//        $chromeOptions = new ChromeOptions();
//        $chromeOptions->setExperimentalOption('w3c', false);

        $options = [
            'port' => mt_rand(9500, 9600),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
//            'capabilities' => [
//                ChromeOptions::CAPABILITY => $chromeOptions
//            ]
        ];

        $this->comicClient = Client::createChromeClient(null, $args, $options);
        $this->comicIssueClient = Client::createChromeClient(null, $args, $options);
    }

    /**
     * @throws Exception
     */
    public function importComic(
        string $slug,
        string $language = PlatformUtil::LANGUAGE_EN
    ): ?Comic
    {
        /** @var ComicLanguage|null $comicLanguage */
        $comicLanguage = $this->em->getRepository(ComicLanguage::class)->findOneBySlugAndLanguage($slug, $language);

        if ($comicLanguage === null) {
            return null;
        }

        foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
            $this->importComicPlatform($comicPlatform);
        }

        $this->logger->info('Client closed');

        return $comicLanguage->getComic();
    }

    /**
     * @throws Exception
     */
    public function importComicPlatform(
        ComicPlatform $comicPlatform
    ): void
    {
        $this->openUrl(self::COMIC_CLIENT, $comicPlatform->getUrl());
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            throw new Exception('Platform not found');
        }

        try {
            $comicLanguage = $comicPlatform->getComicLanguage();
            $comic = $comicLanguage->getComic();

            if (empty($comic->getTitle())) {
                $title = $this->findNode(self::COMIC_CLIENT, $platform->getTitleNode());
                if (!empty($title)) {
                    $comic->setTitle($title);
                }
            }

            if (empty($comic->getAuthor())) {
                $author = $this->findNode(self::COMIC_CLIENT, $platform->getAuthorNode());
                if (!empty($author)) {
                    $comic->setAuthor($author);
                }
            }

            if ($comic->getImage() === null) {
                $imageUrl = $this->findNode(self::COMIC_CLIENT, $platform->getMainImageNode());
                if (!empty($imageUrl)) {
                    $imageEntity = $this->imageService->uploadMangaImage($imageUrl, [ 'Referer: ' . $platform->getBaseUrl() ]);
                    $comic->setImage($imageEntity);
                }
            }

            if (empty($comicLanguage->getDescription())) {
                $description = $this->findNode(self::COMIC_CLIENT, $platform->getDescriptionNode());
                if (!empty($description)) {
                    $comicLanguage->setDescription($description);
                }
            }

            $status = $this->findNode(self::COMIC_CLIENT, $platform->getStatusNode());
            if (!empty($status)) {
                $comic->setStatus($status);
            }

            $this->em->flush();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->comicClient->quit();

        $this->importComicIssues($comicPlatform);
    }

    /**
     * @throws Exception
     */
    public function importComicIssues(ComicPlatform $comicPlatform, int $offset = 0, int $chapterNumber = null)
    {
        $this->openUrl(self::COMIC_CLIENT, $comicPlatform->getUrl());
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            return null;
        }

        $issues = $this->findNode(self::COMIC_CLIENT, $platform->getComicIssuesDataNode(), ['offset' => $offset, 'chapterNumber' => $chapterNumber]);
        if (empty($issues)) {
            $this->logger->error('No comic issues');
            return;
        }
        $this->logger->info('Issues fetched.');
        $this->comicClient->quit();

        foreach ($issues as $issueData) {
            $this->logger->info('Issue: ' . $issueData['number']);
            $comicIssue = $this->em->getRepository(ComicIssue::class)->findOneBy([
                'number' => $issueData['number'],
                'comicLanguage' => $comicPlatform->getComicLanguage()
            ]);

            $new = false;
            if (empty($comicIssue)) {
                $comicIssue = new ComicIssue();
                $comicIssue
                    ->setTitle($issueData['title'])
                    ->setNumber($issueData['number'])
                    ->setType(ComicIssue::TYPE_CHAPTER)
                    ->setComicLanguage($comicPlatform->getComicLanguage())
                    ->setDate($issueData['date']);

                $this->em->persist($comicIssue);
                $new = true;
            }

            if ($comicIssue->getComicPages()->isEmpty()) {
                $this->importComicIssueImages($comicIssue, $platform, $issueData['url']);
            }

            $this->em->flush();

            $message = '[COMIC-ISSUE] Already added:';
            if ($new) {
                $message = '[COMIC-ISSUE] Added:';
            }
            $this->logger->info($message . $comicIssue->getNumber());
        }
        $this->logger->info('Chapters done');
    }

    /**
     * @throws Exception
     */
    public function importComicIssueImages(ComicIssue $comicIssue, PlatformInterface $platform, $issueUrl)
    {
        $this->openUrl(self::COMIC_ISSUE_CLIENT, $platform->getBaseUrl() . $issueUrl);
        $issuePages = $this->findNode(self::COMIC_ISSUE_CLIENT, $platform->getComicIssuesDataNode());

        $this->logger->info(count($issuePages) . ' pages fetched');
        if (empty($issuePages)) {
            $this->logger->error('No pages');
        }
        $this->comicIssueClient->quit();

        foreach ($issuePages as $issuePageData) {
            $file = $this->imageService->uploadChapterImage($issuePageData['url'], $platform->getHeaders());

            if ($file instanceof File) {
                $comicPage = new ComicPage();
                $comicPage
                    ->setFile($file)
                    ->setNumber($issuePageData['number'])
                    ->setComicIssue($comicIssue);

                $this->em->persist($comicPage);
            } else {
                $this->logger->error('Page #' . $issuePageData['number'] . ' not uploaded');
                $comicIssue->removeAllComicPages();
                break;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getMissingImportChapters(
        string $comicSlug,
        string $language = PlatformUtil::LANGUAGE_EN
    ): array
    {
        /** @var array $comicIssues */
        $comicIssues = $this->em->getRepository(ComicIssue::class)->getComicIssuesBySlugAndLanguage($comicSlug, $language);

        if (empty($comicIssues)) {
            throw new Exception('Comic issues not found');
        }

        $comicIssueNumbers = array_map(function (ComicIssue $comicIssue) {
            return $comicIssue->getNumber();
        }, array_filter($comicIssues, function (ComicIssue $comicIssue) {
            return !$comicIssue->getComicPages()->isEmpty();
        }));

        $max = max($comicIssueNumbers);

        $missingChapters = array_diff(
            range(0, $max),
            $comicIssueNumbers
        );

        $this->logger->info('[COMIC-ISSUE]: Missing chapters for ' . $comicSlug . ': ' . (count($missingChapters) ? implode(',', $missingChapters) : 'None'));
        return $missingChapters;
    }

    /**
     * @throws Exception
     */
    public function openUrl(string $client, string $url): void
    {
        /** @var Client $client */
        $client = $this->$client;

        if ($client instanceof Client && $client->getCurrentURL() !== $url) {
            $this->logger->info("[URL] opening $url");
            $client->request('GET', $url);
            $this->logger->info("[URL] $url opened.");
        }
    }

    /** Todo: Est-ce que j'ai besoin de retourner $node ? */
    public function findNode(string $client, PlatformNode $platformNode, array $callbackParameters = [])
    {
        $returnValue = null;
        $this->logger->info("[$client]: " . $platformNode->getName());
        if (!empty($selector = $platformNode->getSelector())) {
            /** @var Crawler $crawler */
            $crawler = $this->$client->getCrawler();

            $node = $crawler->filter($selector);

            if ($platformNode->hasCallback()) {
                $returnValue = $platformNode->executeCallback($node, $callbackParameters);
            }

            if ($platformNode->isText()) {
                $returnValue = $node->getText();
            }

            if (!empty($attribute = $platformNode->getAttribute())) {
                $returnValue = $node->getAttribute($attribute);
            }
        }

        if ($platformNode->hasScript()) {
            $returnValue = $platformNode->executeScript($this->$client, $callbackParameters);
        }

        $this->logger->debug("[{$platformNode->getName()}]: " . (is_array($returnValue) ? count($returnValue) . ' values' : $returnValue) );

        return $returnValue;
    }
}