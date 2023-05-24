<?php

namespace App\Service;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPage;
use App\Entity\ComicPlatform;
use App\Entity\File;
use App\MangaPlatform\PlatformInterface;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class ImportService
{
    private EntityManagerInterface $em;

    private LoggerInterface $logger;

    private ImageService $imageService;

    private CrawlService $crawlService;

    private array $executionDetail = [
        'comic' => [
            'id' => null,
            'title' => '',
            'issues' => [
                'detected' => 0,
                'added' => 0,
                'updated' => 0
            ]
        ],
        'errors' => []
    ];

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        CrawlService $crawlService,
        ImageService $imageService
    )
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->crawlService = $crawlService;
        $this->imageService = $imageService;
    }

    /**
     * @throws Exception
     */
    public function importComic(
        string $slug,
        string $language = PlatformUtil::LANGUAGE_EN,
        int $offset = 0,
        int $issueNumber = null
    ): array
    {
        /** @var ComicLanguage|null $comicLanguage */
        $comicLanguage = $this->em->getRepository(ComicLanguage::class)->findOneBySlugAndLanguage($slug, $language);

        if ($comicLanguage === null) {
            $this->executionDetail['errors'][] = ['no_comic_language'];
            return $this->executionDetail;
        }

        $this->executionDetail['comic']['id'] = $comicLanguage->getComic()->getId();
        $this->executionDetail['comic']['title'] = $comicLanguage->getComic()->getTitle();

        foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
            $this->importComicPlatform($comicPlatform, $offset, $issueNumber);
        }

        return $this->executionDetail;
    }

    /**
     * @throws Exception
     */
    public function importComicPlatform(
        ComicPlatform $comicPlatform,
        int $offset = 0,
        int $issueNumber = null
    ): void
    {
        $this->crawlService->openUrl($comicPlatform->getUrl());
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            throw new Exception('Platform not found');
        }

        try {
            $comicLanguage = $comicPlatform->getComicLanguage();
            $comic = $comicLanguage->getComic();

            if (empty($comic->getTitle())) {
                $title = $this->crawlService->findNode($platform->getTitleNode());
                if (!empty($title)) {
                    $comic->setTitle($title);
                }
            }

            if (empty($comic->getAuthor())) {
                $author = $this->crawlService->findNode($platform->getAuthorNode());
                if (!empty($author)) {
                    $comic->setAuthor($author);
                }
            }

            if ($comic->getImage() === null) {
                $imageUrl = $this->crawlService->findNode($platform->getMainImageNode());
                $this->logger->info('[Comic] Getting image from url: ' . $imageUrl);
                if (!empty($imageUrl)) {
                    $imageEntity = $this->imageService->uploadComicImage($imageUrl, [ 'Referer: ' . $platform->getBaseUrl() ]);
                    $comic->setImage($imageEntity);
                }
            }

            if (empty($comicLanguage->getDescription())) {
                $description = $this->crawlService->findNode($platform->getDescriptionNode());
                if (!empty($description)) {
                    $comicLanguage->setDescription($description);
                }
            }

            $status = $this->crawlService->findNode($platform->getStatusNode());
            if (!empty($status)) {
                $comic->setStatus($status);
            }

            $this->em->flush();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->crawlService->closeClient();

        $this->importComicIssues($comicPlatform, $offset, $issueNumber);
    }

    /**
     * @throws Exception
     */
    public function importComicIssues(ComicPlatform $comicPlatform, int $offset = 0, int $chapterNumber = null): void
    {
        try {
            $this->crawlService->openUrl($comicPlatform->getUrl());
        } catch (Exception $e) {
            $this->executionDetail['errors']['comic'][] = ['comicIssues' => $comicPlatform->getUrl()];
            $this->logger->error('[CRAWL][ERROR] ' . $e->getMessage());
            return;
        }

        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            $this->logger->error('ComicPlatform ' . $comicPlatform->getPlatform()->getName() . ' not found !');
            return;
        }

        $issues = $this->crawlService->findNode($platform->getComicIssuesDataNode(), ['offset' => $offset, 'chapterNumber' => $chapterNumber]);

        if (empty($issues)) {
            $this->logger->error('No comic issues.');
            return;
        }
        $this->logger->info('Issues fetched.');
        $this->executionDetail['comic']['issues']['detected'] = count($issues);
        $this->crawlService->closeClient();

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

            // TODO: Add force argument to reupload images
            if ($comicIssue->getComicPages()->isEmpty()) {
                $this->importComicIssueImages($comicIssue, $platform, $issueData['url']);
            }

            $this->em->flush();

            $message = '[COMIC-ISSUE] Already added:';
            if ($new) {
                $message = '[COMIC-ISSUE] Added:';
                $this->executionDetail['comic']['issues']['added']++;
            }
            $this->logger->info($message . $comicIssue->getNumber());
        }
        $this->logger->info('Chapters done');
    }

    /**
     * @throws Exception
     */
    public function importComicIssueImages(ComicIssue $comicIssue, PlatformInterface $platform, $issueUrl): void
    {
        try {
            $this->crawlService->openUrl($issueUrl);
        } catch (Exception $e) {
            $this->executionDetail['errors']['comicIssue'][] = ['url' => $issueUrl, 'comicIssue' => $comicIssue->getId()];
            $this->logger->error('[CRAWL][ERROR] ' . $e->getMessage());
            return;
        }

        $issuePages = $this->crawlService->findNode($platform->getComicPagesNode());

        $this->logger->info(count($issuePages) . ' pages fetched');
        if (empty($issuePages)) {
            $this->logger->error('No pages');
        }
        $this->crawlService->closeClient();

        $this->logger->info('[Comic] Getting comic issue images');

        foreach ($issuePages as $issuePageData) {
            $file = $this->imageService->uploadIssueImage($issuePageData['url'], $platform->getHeaders());

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
}