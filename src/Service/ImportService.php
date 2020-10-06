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
use Psr\Http\Client\ClientExceptionInterface;
use App\Utils\Platform as UtilPlatform;
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

    public function createManga($mangaUrl, $mangaSlug) {
        $this->mangaDom->loadFromUrl($mangaUrl);

        $platform = UtilPlatform::findPlatformFromUrl($mangaUrl);
        $nodes = $platform['nodes'];
        /** @var Platform $platformEntity */
        $platformEntity = $this->em->getRepository(Platform::class)->findOneBy([
            'name' => $platform['name']
        ]);

        $title = $this->findNode(self::MANGA_DOM, $nodes['titleNode']);
        $slug = Functions::slugify($title);

        $status = $this->findNode(self::MANGA_DOM, $nodes['statusNode']);
        $altTitles = $this->findNode(self::MANGA_DOM, $nodes['altTitlesNode']);

        $manga = new Manga();
        $manga
            ->setStatus($status)
            ->setTitle($title)
            ->setSlug($slug)
            ->setAltTitles($altTitles);

        $this->em->persist($manga);

        $mangaPlatform = new MangaPlatform();
        $mangaPlatform->setPlatform($platformEntity)
            ->setManga($manga)
            ->setSourceSlug($mangaSlug)
            ->setSourceUrl($mangaUrl);

        $mangaImageUrl = $this->findNode(self::MANGA_DOM, $nodes['mangaImageNode']);
        $mangaImage = $this->imageService->uploadMangaImage($mangaImageUrl);
        $manga->setImage($mangaImage);

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
        $platform = UtilPlatform::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];
        $this->mangaDom->loadFromUrl($mangaUrl);

        $description = $this->findNode(self::MANGA_DOM, $nodes['descriptionNode']);
        if ($description) {
            $mangaPlatform->setDescription($description);
        }

        $views = $this->findNode(self::MANGA_DOM, $nodes['viewsNode']);
        if ($views) {
            $mangaPlatform->setViewsCount($views);
        }

        $lastUpdated = $this->findNode(self::MANGA_DOM, $nodes['lastUpdateNode']);
        if ($lastUpdated) {
            $mangaPlatform->setLastUpdated($lastUpdated);
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
        $platform = UtilPlatform::getPlatform($mangaPlatform->getPlatform());
        $nodes = $platform['nodes'];

        /** @var Dom\Node\Collection $chapters */
        $chaptersData = $this->findNode(self::MANGA_DOM, $nodes['chapterDataNode'], ['offset' => $offset, 'chapterNumber' => $chapterNumber]);

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
            }

            if ($chapter->getChapterPages()->isEmpty() && $addImages) {
                $chapter->removeAllChapterPages();

                $this->chapterDom->loadFromUrl($chapterData['url']);
                $chapterPagesData = $this->findNode(self::CHAPTER_DOM, $nodes['chapterPagesNode'], ['chapter' => $chapter]);
                foreach ($chapterPagesData as $pageData) {
                    $file = $this->imageService->uploadChapterImage($pageData['url'], $pageData['imageHeaders']);
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
        $node = $this->$dom->find($platformNode['selector'], $platformNode['child-index'] ?? null);

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
}
