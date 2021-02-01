<?php


namespace App\Service;


use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\Platform;
use App\Utils\Functions;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Psr\Http\Client\ClientExceptionInterface;
use App\Utils\PlatformUtil;
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

        $this->mangaClient = Client::createChromeClient();
    }

    /**
     * @param string $url
     * @param string $mangaSlug
     * @param int $offset
     * @param int|null $chapter
     * @param bool $addImages
     * @return MangaPlatform|null
     * @throws ClientExceptionInterface
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
        $this->mangaClient->request('GET', $mangaUrl);

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

            $mangaImageUrl = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::MANGA_IMAGE_NODE]);
            $mangaImage = $this->imageService->uploadMangaImage($mangaImageUrl);
            $manga->setImage($mangaImage);

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
        $this->mangaClient->request('GET', $mangaUrl);

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
        $this->mangaClient->request('GET', $mangaUrl);
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

                $this->mangaClient->request('GET', $chapter->getSourceUrl());
                $chapterPagesData = $this->findNode(self::MANGA_CLIENT, $nodes[PlatformUtil::CHAPTER_PAGES_NODE], ['chapter' => $chapter]);

                if ($chapterPagesData) {
                    foreach ($chapterPagesData as $pageData) {
                        $file = $this->imageService->uploadChapterImage($pageData['url'], $pageData['imageHeaders'] ?? []);
                        $chapterPage = new ChapterPage();
                        $chapterPage
                            ->setFile($file)
                            ->setNumber($pageData['number'])
                            ->setChapter($chapter);

                        $this->em->persist($chapterPage);
                    }
                }
            }

            $this->em->flush();

            $logInfos = [
                'number' => $chapter->getNumber()
            ];

            if ($new) {
                $this->logger->info('New chapter added: ' . $logInfos['number']);
            } else {
                $this->logger->info('Chapter already added: ' . $logInfos['number']);
            }
        }

        $this->em->flush();
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
                /** @var RemoteWebElement $item */

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
}
