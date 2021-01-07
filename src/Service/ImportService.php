<?php


namespace App\Service;


use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\Platform;
use App\Utils\Functions;
use Doctrine\ORM\EntityManagerInterface;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Options;
use PhpParser\Node;
use Psr\Http\Client\ClientExceptionInterface;
use App\Utils\PlatformUtil;
use Psr\Log\LoggerInterface;

class ImportService
{
    /** @var Dom $mangaDom */
    protected $mangaDom;

    /** @var Dom $chapterDom */
    protected $chapterDom;

    /** @var EntityManagerInterface $em */
    protected $em;

    /** @var ImageService $imageService  */
    protected $imageService;

    /** @var LoggerInterface $logger */
    protected $logger;

    public const MANGA_DOM = 'mangaDom';
    public const CHAPTER_DOM = 'chapterDom';

    public function __construct(EntityManagerInterface $em, ImageService $imageService, LoggerInterface $logger) {
        $this->em = $em;
        $this->imageService = $imageService;
        $this->logger = $logger;

        $this->mangaDom = new Dom();
        $this->chapterDom = new Dom();
    }

    /**
     * @param string $url
     * @param string $mangaSlug
     * @param int $offset
     * @param int|null $chapter
     * @param bool $addImages
     * @return MangaPlatform|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function importManga(string $url, string $mangaSlug, int $offset = 0, int $chapter = null, bool $addImages = false) {
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
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function createManga($mangaUrl, $mangaSlug): MangaPlatform
    {
        $options = new Options();
        $options->setRemoveSmartyScripts(true);
        $options->setRemoveScripts(true);
        $this->mangaDom->loadFromUrl($mangaUrl, $options);

        $platform = PlatformUtil::findPlatformFromUrl($mangaUrl);
        $nodes = $platform['nodes'];
        /** @var Platform $platformEntity */
        $platformEntity = $this->em->getRepository(Platform::class)->findOneBy([
            'name' => $platform['name']
        ]);

        $title = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::TITLE_NODE]);
        if (array_key_exists(PlatformUtil::ALT_TITLES_NODE, $nodes)) {
            $altTitles = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::ALT_TITLES_NODE]);
        }

        $manga = $this->em->getRepository(Manga::class)->findOneByAltTitles($title, $altTitles ?? []);

        if (!($manga instanceof Manga)) {
            $slug = Functions::slugify($title);

            $status = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::STATUS_NODE]);

            $manga = new Manga();
            $manga
                ->setStatus($status)
                ->setTitle($title)
                ->setSlug($slug);

            $mangaImageUrl = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::MANGA_IMAGE_NODE]);
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
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function fillManga(?MangaPlatform $mangaPlatform) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];
        $this->mangaDom->loadFromUrl($mangaUrl);

        if (array_key_exists(PlatformUtil::AUTHOR_NODE, $nodes)) {
            $author = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::AUTHOR_NODE]);
            if ($author) {
                $mangaPlatform->setAuthor($author);
            }
        }

        if (array_key_exists(PlatformUtil::DESCRIPTION_NODE, $nodes)) {
            $description = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::DESCRIPTION_NODE]);
            if ($description) {
                $mangaPlatform->setDescription($description);
            }
        }

        if (array_key_exists(PlatformUtil::VIEWS_NODE, $nodes)) {
            $views = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::VIEWS_NODE]);
            if ($views) {
                $mangaPlatform->setViewsCount($views);
            }
        }

        if (array_key_exists(PlatformUtil::LAST_UPDATE_NODE, $nodes)) {
            $lastUpdated = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::LAST_UPDATE_NODE]);
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
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    public function importChapters(MangaPlatform $mangaPlatform, int $offset = 0, int $chapterNumber = null, bool $addImages = false) {
        $mangaUrl = $mangaPlatform->getSourceUrl();
        $this->mangaDom->loadFromUrl($mangaUrl);
        $platform = PlatformUtil::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];

        /** @var Dom\Node\Collection $chapters */

        $chaptersData = $this->findNode(self::MANGA_DOM, $nodes[PlatformUtil::CHAPTER_DATA_NODE], ['offset' => $offset, 'chapterNumber' => $chapterNumber]);

        dump($chaptersData);
        exit;
        foreach ($chaptersData as $chapterData) {
            $chapter = $this->em->getRepository(Chapter::class)->findOneBy([
                'number' => $chapterData['number'],
                'manga' => $mangaPlatform
            ]);

            if (!$chapter) {
                $chapter = new Chapter();
                $chapter
                    ->setTitle($chapterData['title'])
                    ->setNumber($chapterData['number'])
                    ->setDate($chapterData['date'])
                    ->setSourceUrl($chapterData['url'])
                    ->setManga($mangaPlatform);

                $this->em->persist($chapter);
                $this->em->flush();
            }

            if ($chapter->getChapterPages()->isEmpty() && $addImages) {
                $chapter->removeAllChapterPages();

                $this->chapterDom->loadFromUrl($chapter->getSourceUrl());
                $chapterPagesData = $this->findNode(self::CHAPTER_DOM, $nodes[PlatformUtil::CHAPTER_PAGES_NODE], ['chapter' => $chapter]);
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

            $logInfos = [
                'number' => $chapter->getNumber()
            ];
            if (!$chapter->getId()) {
                $this->logger->info('New chapter added', $logInfos);
            } else {
                $this->logger->info('Chapter already added', $logInfos);
            }
        }

        $this->em->flush();
    }

    public function findNode($dom, $platformNode, array $callbackParameters = []) {
        if (isset($platformNode['selector'])) {
            if (!is_array($platformNode['selector'])) {
                $platformNode['selector'] = [$platformNode['selector']];
            }

            $node = $this->$dom;
            foreach ($platformNode['selector'] as $selector => $index) {
                $node = $node->find($selector, $index ?? null);
            }

            if (isset($platformNode['callback'])) {
                return $platformNode['callback']($node, $callbackParameters);
            }

            if (isset($platformNode['text']) && $platformNode['text']) {
                return $node->text;
            }

            if (isset($platformNode['attribute'])) {
                return $node->getAttribute($platformNode['attribute']);
            }

            return $node;
        }

        return null;
    }
}
