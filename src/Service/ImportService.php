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
    /** @var EntityManagerInterface $em */
    private $em;

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var ImageService $imageService */
    private $imageService;

    /** @var CrawlService $crawlService */
    private $crawlService;

    /** @var array $comicIssues */
    private $comicIssues = [];

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

        return $comicLanguage->getComic();
    }

    /**
     * @throws Exception
     */
    public function importComicPlatform(
        ComicPlatform $comicPlatform
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
                if (!empty($imageUrl)) {
                    $imageEntity = $this->imageService->uploadMangaImage($imageUrl, [ 'Referer: ' . $platform->getBaseUrl() ]);
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

        $this->importComicIssues($comicPlatform);
    }

    /**
     * @throws Exception
     */
    public function importComicIssues(ComicPlatform $comicPlatform, int $offset = 0, int $chapterNumber = null)
    {
        $this->crawlService->openUrl($comicPlatform->getUrl());
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            return null;
        }

        $issues = $this->crawlService->findNode($platform->getComicIssuesDataNode(), ['offset' => $offset, 'chapterNumber' => $chapterNumber]);
        if (empty($issues)) {
            $this->logger->error('No comic issues');
            return;
        }
        $this->logger->info('Issues fetched.');
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
        $this->crawlService->openUrl($platform->getBaseUrl() . $issueUrl);
        $issuePages = $this->crawlService->findNode($platform->getComicIssuesDataNode());

        $this->logger->info(count($issuePages) . ' pages fetched');
        if (empty($issuePages)) {
            $this->logger->error('No pages');
        }
        $this->crawlService->closeClient();

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
}