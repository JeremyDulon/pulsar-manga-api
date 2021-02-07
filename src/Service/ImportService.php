<?php


namespace App\Service;


use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\Platform;
use App\Utils\Functions;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PlatformUtil;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ImportService
{
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
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
        ];
        $this->mangaClient = Client::createChromeClient(null, $args, ['port' => 9514]);
    }

    /**
     * @param string $url
     * @param string $mangaSlug
     * @param int $offset
     * @param int|null $chapter
     * @param bool $addImages
     * @return MangaPlatform|null
     */
    public function importManga(string $url, string $mangaSlug, int $offset = 0, int $chapter = null, bool $addImages = false): ?MangaPlatform
    {
        /** @var MangaPlatform|null $mangaPlatform */
        $mangaPlatform = $this->em->getRepository(MangaPlatform::class)->findOneBy([
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
     * @return MangaPlatform
     *
     */
    public function createManga($mangaUrl, $mangaSlug): MangaPlatform
    {
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);

        $platform = PlatformUtil::findPlatformFromUrl($mangaUrl);
        $nodes = $platform['nodes'];
        /** @var Platform $platformEntity */
        $platformEntity = $this->em->getRepository(Platform::class)->findOneBy([
            'name' => $platform['name']
        ]);

        $title = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::TITLE_NODE]);

        if (array_key_exists(PlatformUtil::ALT_TITLES_NODE, $nodes)) {
            $altTitles = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::ALT_TITLES_NODE]);
        }

        $manga = $this->em->getRepository(Manga::class)->findOneByAltTitles($title, $altTitles ?? []);

        if (!($manga instanceof Manga)) {
            $slug = Functions::slugify($title);

            $status = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::STATUS_NODE]);

            $manga = new Manga();
            $manga
                ->setStatus($status)
                ->setTitle($title)
                ->setSlug($slug);

            $this->addMangaImage($manga);

            $this->em->persist($manga);
        }

        if (isset($altTitles)) {
            $manga->setAltTitles(array_merge($manga->getAltTitles() ?? [], $altTitles ?? []));
        }

        $mangaPlatform = new MangaPlatform();
        $mangaPlatform->setPlatform($platformEntity)
            ->setManga($manga)
            ->setSourceSlug($mangaSlug)
            ->setSourceUrl($mangaUrl);

        $this->em->persist($mangaPlatform);

        $this->em->flush();

        return $mangaPlatform;
    }

    /**
     * @param MangaPlatform|null $mangaPlatform
     */
    public function fillManga(?MangaPlatform $mangaPlatform) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);

        if (array_key_exists(PlatformUtil::AUTHOR_NODE, $nodes)) {
            $author = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::AUTHOR_NODE]);
            if ($author) {
                $mangaPlatform->setAuthor($author);
            }
        }

        if (array_key_exists(PlatformUtil::DESCRIPTION_NODE, $nodes)) {
            $description = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::DESCRIPTION_NODE]);
            if ($description) {
                $mangaPlatform->setDescription($description);
            }
        }

        if (array_key_exists(PlatformUtil::VIEWS_NODE, $nodes)) {
            $views = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::VIEWS_NODE]);
            if ($views) {
                $mangaPlatform->setViewsCount($views);
            }
        }

        if (array_key_exists(PlatformUtil::LAST_UPDATE_NODE, $nodes)) {
            $lastUpdated = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::LAST_UPDATE_NODE]);
            if ($lastUpdated) {
                $mangaPlatform->setLastUpdated($lastUpdated);
            }
        }

        $this->em->flush();
    }

    /**
     * @param MangaPlatform $mangaPlatform
     * @param int $offset
     * @param int|null $chapterNumber
     * @param bool $addImages
     *
     */
    public function importChapters(MangaPlatform $mangaPlatform, int $offset = 0, int $chapterNumber = null, bool $addImages = false) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $this->openUrl(self::MANGA_CLIENT, $mangaUrl);
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];

        $chaptersData = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::CHAPTER_DATA_NODE], ['offset' => $offset, 'chapterNumber' => $chapterNumber]);

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
        $nodes = $platform['nodes'];

        $this->openUrl(self::MANGA_CLIENT, $chapter->getSourceUrl());
        $chapterPagesData = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::CHAPTER_PAGES_NODE], ['chapter' => $chapter]);

        if ($chapterPagesData) {

            $countPages = count($chapterPagesData);
            $this->logger->info("[CHAPTER] $countPages pages to add.");

            foreach ($chapterPagesData as $pageData) {
                $file = $this->imageService->uploadChapterImage($pageData['url'], $pageData['imageHeaders'] ?? []);
                $this->logger->info('[CHAPTER] Page added. ' . ($countPages - $pageData['number']) . ' to go.');
                $chapterPage = new ChapterPage();
                $chapterPage
                    ->setFile($file)
                    ->setNumber($pageData['number'])
                    ->setChapter($chapter);

                $this->em->persist($chapterPage);
            }
        }
    }

    /**
     * @param string $client
     * @param array $platformNode
     * @param array $callbackParameters
     * @return mixed
     */
    public function findNode(string $client, array $platformNode, array $callbackParameters = [])
    {
        if (isset($platformNode['selector'])) {
            /** @var Crawler $crawler */
            $crawler = $this->$client->getCrawler();

            $node = $crawler->filter($platformNode['selector']);

            if (isset($platformNode['callback'])) {
                return $platformNode['callback']($node, $callbackParameters);
            }

            if (isset($platformNode['text']) && $platformNode['text'] === true) {
                return $node->getText();
            }

            if (isset($platformNode['attribute'])) {
                return $node->getAttribute($platformNode['attribute']);
            }

            return $node;
        }

        if (isset($platformNode['script_callback'])) {
            return $platformNode['script_callback']($this->$client, $callbackParameters);
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

    public function addMangaImage(MangaPlatform $mangaPlatform) {
        if (empty($mangaPlatform->getManga()->getImage())) {
            $platform = PlatformUtil::findPlatformFromUrl($mangaPlatform->getSourceUrl());
            $nodes = $platform['nodes'];
            $this->openUrl(self::MANGA_CLIENT, $mangaPlatform->getSourceUrl());

            $mangaImageUrl = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::MANGA_IMAGE_NODE]);
            $mangaImage = $this->imageService->uploadMangaImage($mangaImageUrl);
            $mangaPlatform->getManga()->setImage($mangaImage);
        }
    }
}
