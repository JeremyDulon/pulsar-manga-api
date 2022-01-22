<?php


namespace App\Service;


use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\File;
use App\Entity\Manga;
use App\Entity\ComicPlatform;
use App\Entity\Platform;
use App\MangaPlatform\AbstractPlatform;
use App\MangaPlatform\PlatformNode;
use App\Utils\Functions;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PlatformUtil;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ImportService
{
    // Remake: this
    /** @var Client $mangaClient */
    protected $mangaClient;

    /** @var Client $chapterClient */
    protected $chapterClient;

    /** @var EntityManagerInterface $em */
    protected $em;

    /** @var ImageService $imageService  */
    protected $imageService;

    /** @var LoggerInterface $logger */
    protected $logger;

    public const MANGA_CLIENT = 'mangaClient';
    public const CHAPTER_CLIENT = 'chapterClient';

    public function __construct(EntityManagerInterface $em, ImageService $imageService, LoggerInterface $logger) {
        $this->em = $em;
        $this->imageService = $imageService;
        $this->logger = $logger;

        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox"
        ];

        $options = [
            'port' => mt_rand(9500, 9999),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
        ];
        $this->mangaClient = Client::createChromeClient(null, $args, $options);
    }

    /**
     * @param string $url
     * @param string $mangaSlug
     * @param int $offset
     * @param int|null $chapter
     * @param bool $addImages
     * @return ComicPlatform|null
     */
    public function importManga(string $url, string $mangaSlug, int $offset = 0, int $chapter = null, bool $addImages = false): ?ComicPlatform
    {
        /** @var ComicPlatform|null $mangaPlatform */
        $mangaPlatform = $this->em->getRepository(ComicPlatform::class)->findOneBy([
            'sourceUrl' => $url
        ]);

        if (!$mangaPlatform) {
            $mangaPlatform = $this->createManga($url, $mangaSlug);
        }

        $this->fillManga($mangaPlatform);

        $this->importChapters($mangaPlatform, $offset, $chapter, $addImages);

        return $mangaPlatform;
    }

    /**
     * @param $mangaUrl
     * @param $mangaSlug
     * @return ComicPlatform
     *
     * @throws Exception
     */
    public function createManga($mangaUrl, $mangaSlug): ComicPlatform
    {
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);

        /** @var AbstractPlatform $platform */
        $platform = PlatformUtil::findPlatformFromUrl($mangaUrl);
        /** @var Platform $platformEntity */
        $platformEntity = $this->em->getRepository(Platform::class)->findOneBy([
            'name' => $platform->getName()
        ]);

        if (!$platformEntity) {
            $platformEntity = new Platform();
            $platformEntity->setName($platform->getName());
            $platformEntity->setLanguage($platform->getLanguage());
            $platformEntity->setBaseUrl($platform->getBaseUrl());

            $this->em->persist($platformEntity);
        }

        $title = $this->findNode(self::MANGA_CLIENT, $platform->getTitleNode());


        if ($platform->getAltTitlesNode()->isInit() === true) {
            $altTitles = $this->findNode(self::MANGA_CLIENT, $platform->getAltTitlesNode());
        }

        $manga = $this->em->getRepository(Manga::class)->findOneByAltTitles($title, $altTitles ?? []);

        if (!($manga instanceof Manga)) {
            $slug = Functions::slugify($title);

            $status = $this->findNode(self::MANGA_CLIENT, $platform->getStatusNode());

            $manga = new Manga();
            $manga
                ->setStatus($status)
                ->setTitle($title)
                ->setSlug($slug);

            $this->em->persist($manga);
        }

        if (isset($altTitles)) {
            $manga->setAltTitles(array_merge($manga->getAltTitles() ?? [], $altTitles ?? []));
        }

        $mangaPlatform = new ComicPlatform();
        $mangaPlatform->setPlatform($platformEntity)
            ->setManga($manga)
            ->setSourceSlug($mangaSlug)
            ->setSourceUrl($mangaUrl);

        $this->addMangaImage($mangaPlatform);

        $this->em->persist($mangaPlatform);

        $this->em->flush();

        return $mangaPlatform;
    }

    /**
     * @param ComicPlatform|null $mangaPlatform
     */
    public function fillManga(?ComicPlatform $mangaPlatform) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);

        if ($platform->getAuthorNode()->isInit() === true) {
            $author = $this->findNode(self::MANGA_CLIENT, $platform->getAuthorNode());
            if ($author) {
                $mangaPlatform->setAuthor($author);
            }
        }

        if ($platform->getDescriptionNode()->isInit() === true) {
            $description = $this->findNode(self::MANGA_CLIENT, $platform->getDescriptionNode());
            if ($description) {
                $mangaPlatform->setDescription($description);
            }
        }

        if ($platform->getViewsNode()->isInit() === true) {
            $views = $this->findNode(self::MANGA_CLIENT, $platform->getViewsNode());
            if ($views) {
                $mangaPlatform->setViewsCount($views);
            }
        }

        if ($platform->getLastUpdatedNode()->isInit() === true) {
            $lastUpdated = $this->findNode(self::MANGA_CLIENT, $platform->getLastUpdatedNode());
            if ($lastUpdated) {
                $mangaPlatform->setLastUpdated($lastUpdated);
            }
        }

        $this->em->flush();
    }

    /**
     * @param ComicPlatform $mangaPlatform
     * @param int $offset
     * @param int|null $chapterNumber
     * @param bool $addImages
     *
     */
    public function importChapters(ComicPlatform $mangaPlatform, int $offset = 0, int $chapterNumber = null, bool $addImages = false) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());

        $chaptersData = $this->findNode(self::MANGA_CLIENT, $platform->getChapterDataNode(), ['offset' => $offset, 'chapterNumber' => $chapterNumber]);

        foreach ($chaptersData as $chapterData) {
            $chapter = $this->em->getRepository(Chapter::class)->findOneBy([
                'number' => $chapterData['number'],
                'manga' => $mangaPlatform
            ]);

            if (empty($chapter)) {
                $chapter = new Chapter();
                $chapter
                    ->setTitle($chapterData['title'])
                    ->setNumber($chapterData['number'])
                    ->setDate($chapterData['date'])
                    ->setSourceUrl($chapterData['url'])
                    ->setManga($mangaPlatform);

                $this->em->persist($chapter);
                $new = true;
            } else {
                $new = false;
            }

            if ($chapter->getChapterPages()->isEmpty() && $addImages) {
                $chapter->removeAllChapterPages();

                $this->importChapterImages($chapter);
            }

            $logInfos = [
                'number' => $chapter->getNumber()
            ];

            $this->em->flush();

            if ($new) {
                $this->logger->info('[CHAPTER] Added: ' . $logInfos['number']);
            } else {
                $this->logger->info('[CHAPTER] Already added: ' . $logInfos['number']);
            }
        }
    }

    /**
     * @param Chapter $chapter
     */
    public function importChapterImages(Chapter $chapter) {
        $platform = PlatformUtil::getPlatform($chapter->getManga()->getPlatform());

        $this->openUrl(self::MANGA_CLIENT, $chapter->getSourceUrl());
        $chapterPagesData = $this->findNode(self::MANGA_CLIENT, $platform->getChapterPagesNode(), ['chapter' => $chapter]);

        if ($chapterPagesData) {

            $countPages = count($chapterPagesData);
            $this->logger->info("[CHAPTER] $countPages pages to add.");

            foreach ($chapterPagesData as $pageData) {
                $file = $this->imageService->uploadChapterImage($pageData['url'], $pageData['imageHeaders'] ?? []);
                if ($file instanceof File) {
                    $chapterPage = new ChapterPage();
                    $chapterPage
                        ->setFile($file)
                        ->setNumber($pageData['number'])
                        ->setChapter($chapter);

                    $this->em->persist($chapterPage);
                } else {
                    $this->logger->warning('Page: #' . $pageData['number'] . ' not uploaded');
                    $chapter->removeAllChapterPages();
                    break;
                }
            }
        }
    }

    /**
     * @param string $client
     * @param PlatformNode $platformNode
     * @param array $callbackParameters
     * @return mixed
     */
    public function findNode(string $client, PlatformNode $platformNode, array $callbackParameters = [])
    {
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

    public function openUrl($client, $url) {
        /** @var Client $client */
        $client = $this->$client;

        if ($client instanceof Client && $client->getCurrentURL() !== $url) {
            $this->logger->info("[URL] opening $url");
            $client->request('GET', $url);
            $this->logger->info("[URL] $url opened.");
        }
    }

    public function addMangaImage(ComicPlatform $mangaPlatform) {
        if (empty($mangaPlatform->getManga()->getImage())) {
            $platform = PlatformUtil::findPlatformFromUrl($mangaPlatform->getSourceUrl());
            $this->openUrl(self::MANGA_CLIENT, $mangaPlatform->getSourceUrl());

            $mangaImageUrl = $this->findNode(self::MANGA_CLIENT, $platform->getMangaImageNode());
            if ($mangaImageUrl) {
                $mangaImage = $this->imageService->uploadMangaImage($mangaImageUrl);
                $mangaPlatform->getManga()->setImage($mangaImage);
            }
        }
    }
}
