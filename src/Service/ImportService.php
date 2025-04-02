<?php

namespace App\Service;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPage;
use App\Entity\ComicPlatform;
use App\Entity\File;
use App\Entity\Platform;
use App\Exception\NodeEmptyException;
use App\MangaPlatform\PlatformInterface;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class ImportService
{
    private int $limit;
    private ?int $startingNumber;
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
        'errors' => [],
        'completed' => false
    ];

    private array $issuesImported = [];

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

    public function setLimit(int $limit = 0): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setStartingNumber(int $startingNumber = null): self
    {
        $this->startingNumber = $startingNumber;
        return $this;
    }

    public function getIssuesImported(): array
    {
        return $this->issuesImported;
    }

    public function resetIssuesImported(): void
    {
        $this->issuesImported = [];
    }

    /**
     * @throws Exception
     */
    public function importComic(
        string $slug,
        string $language = PlatformUtil::LANGUAGE_EN
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

        if ($comicLanguage->getComicPlatforms()->isEmpty()) {
            throw new Exception('No Platforms');
        }

        foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
            $this->importComicPlatform($comicPlatform);

            if ($this->executionDetail['completed'] === true) {
                break;
            }
        }

        return $this->executionDetail;
    }

    /**
     * @throws Exception
     */
    public function importComicPlatform(
        ComicPlatform $comicPlatform
    ): void
    {
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            throw new Exception('Platform not found');
        }

        try {
            $this->crawlService->openUrl($comicPlatform->getUrl(), [
                'baseUrl' => $platform->getBaseUrl(),
                'domain' => $platform->getDomain(),
                'cookies' => $platform->getCookies()
            ]);
        } catch (Exception $e) {
            $this->logger->error($e);
            return;
        }

        $comicLanguage = $comicPlatform->getComicLanguage();
        $comic = $comicLanguage->getComic();

        try {
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
        } catch (NodeEmptyException $nodeEmptyException) {
            $this->logger->error($nodeEmptyException->getMessage());
            $comicPlatform->getPlatform()->updateTrust(Platform::TRUST_FACTOR_BAD);
            $this->em->flush();

            return;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return;
        }

        try {
            if ($comic->getImage() === null) {
                $imageUrl = $this->crawlService->findNode($platform->getMainImageNode());
                $this->logger->info('[Comic] Getting image from url: ' . $imageUrl);
                if (!empty($imageUrl)) {
                    $imageEntity = $this->imageService->uploadComicImage($imageUrl, ['Referer: ' . $platform->getBaseUrl()]);
                    $comic->setImage($imageEntity);
                }
            }
        } catch (NodeEmptyException $nodeEmptyException) {
            $this->logger->error($nodeEmptyException->getMessage());
            $comicPlatform->getPlatform()->updateTrust(Platform::TRUST_FACTOR_BAD);
            $this->em->flush();

            return;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return;
        }

        $this->em->flush();

        $this->crawlService->closeClient();

        $this->importComicIssues($comicPlatform);
    }

    /**
     * @throws Exception
     */
    public function importComicIssues(ComicPlatform $comicPlatform, $reimport = false): void
    {
        $platform = PlatformUtil::getPlatform($comicPlatform->getPlatform());

        if ($platform === null) {
            $this->logger->error('ComicPlatform ' . $comicPlatform->getPlatform()->getName() . ' not found !');
            return;
        }

        try {
            $this->crawlService->openUrl($comicPlatform->getUrl(), [
                'domain' => $platform->getDomain(),
                'baseUrl' => $platform->getBaseUrl(),
                'cookies' => $platform->getCookies()
            ]);
        } catch (Exception $e) {
            $this->executionDetail['errors']['comic'][] = ['comicIssues' => $comicPlatform->getUrl()];
            $this->logger->error('[CRAWL][ERROR] ' . $e->getMessage());
            $this->crawlService->closeClient();
            return;
        }

        try {
            $issues = $this->crawlService->findNode($platform->getComicIssuesDataNode(), ['limit' => $this->limit, 'startingNumber' => $this->startingNumber]);
        } catch (NodeEmptyException $nodeEmptyException) {
            $this->logger->error($nodeEmptyException->getMessage());
            $comicPlatform->getPlatform()->updateTrust(Platform::TRUST_FACTOR_BAD);
            $this->em->flush();
            $this->crawlService->closeClient();

            return;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->crawlService->closeClient();
            return;
        } catch (\Error $error) {
            $this->logger->critical($error->getMessage());
            $this->crawlService->closeClient();
            return;
        }

        if (empty($issues)) {
            $this->logger->warning('No new issues for ' . $comicPlatform->getComicLanguage()->getComic()->getTitle() . ' on ' . $platform->getName());
            $this->crawlService->closeClient();
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

            if ($comicIssue !== null && $reimport === false) {
                $this->logger->info('Issue already imported: ' . $comicIssue->getNumber());
                continue;
            }

            $comicIssue = new ComicIssue();
            $comicIssue
                ->setTitle($issueData['title'])
                ->setNumber($issueData['number'])
                ->setType(ComicIssue::TYPE_CHAPTER)
                ->setComicLanguage($comicPlatform->getComicLanguage())
                ->setComicPlatform($comicPlatform)
                ->setQuality(ComicIssue::QUALITY_GOOD)
                ->setDate($issueData['date']);

            $this->em->persist($comicIssue);

            if ($reimport === true) {
                $comicIssue->removeAllComicPages();
            }

            // TODO: Add force argument to reupload images
            if ($comicIssue->getComicPages()->isEmpty()) {
                $issueImagesImported = $this->importComicIssueImages($comicIssue, $platform, $issueData['url']);

                if ($issueImagesImported === false) {
                    $this->logger->error('No images imported');
//                    $comicPlatform->updateTrust(ComicPlatform::TRUST_FACTOR_BAD);
                    $this->em->flush();
                    $this->startingNumber = $issueData['number'];
                    return;
                }

                $comicIssue->setQuality(ComicIssue::QUALITY_GOOD);
            }

            $this->em->flush();

            $this->executionDetail['comic']['issues']['added']++;
            $this->issuesImported[] = $comicIssue;
            $this->logger->info('[COMIC-ISSUE] Added: ' . $comicIssue->getNumber());
            $this->limit--;
        }

        $this->executionDetail['completed'] = true;
    }

    /**
     * @throws Exception
     */
    public function importComicIssueImages(ComicIssue $comicIssue, PlatformInterface $platform, $issueUrl): bool
    {
        try {
            $this->crawlService->openUrl($issueUrl, [
                'domain' => $platform->getDomain(),
                'baseUrl' => $platform->getBaseUrl(),
                'cookies' => $platform->getCookies()
            ]);
        } catch (Exception $e) {
            $this->executionDetail['errors']['comicIssue'][] = ['url' => $issueUrl, 'comicIssue' => $comicIssue->getId()];
            $this->logger->error('[CRAWL][ERROR] ' . $e->getMessage());
            return false;
        }

        try {
            $issuePages = $this->crawlService->findNode($platform->getComicPagesNode());
        } catch (NodeEmptyException $nodeEmptyException) {
            $this->logger->error($nodeEmptyException->getMessage());
            $comicIssue->getComicPlatform()->getPlatform()->updateTrust(Platform::TRUST_FACTOR_BAD);
            $this->em->flush();

            return false;
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }

        if (empty($issuePages)) {
            $this->logger->error('No pages');
            $comicIssue->getComicPlatform()->getPlatform()->updateTrust(Platform::TRUST_FACTOR_BAD);
            return false;
        }

        $this->logger->info(count($issuePages) . ' pages fetched');

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
                // Todo: Peut etre updateTrust ici, en fonction de l'erreur si jamais j'en vois des pertinentes
                $comicIssue->removeAllComicPages();
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     * todo: update this
     */
    public function getMissingImportIssues(
        string $comicSlug,
        string $language = PlatformUtil::LANGUAGE_EN
    ): array
    {
        /** @var array $comicIssues */
        $comicIssues = $this->em->getRepository(ComicIssue::class)->getComicIssuesBySlugAndLanguage($comicSlug, $language);

        dump(count($comicIssues));

        if (empty($comicIssues)) {
            throw new Exception('Comic issues not found');
        }

        $comicIssueNumbers = array_map(function (ComicIssue $comicIssue) {
            return $comicIssue->getNumber();
        }, array_filter($comicIssues, function (ComicIssue $comicIssue) {
            return !$comicIssue->getComicPages()->isEmpty();
        }));

        $max = max($comicIssueNumbers);

        $missingIssues = array_diff(
            range(1, $max),
            $comicIssueNumbers
        );

        $this->logger->info('[COMIC-ISSUE]: Missing issues for ' . $comicSlug . ': ' . (count($missingIssues) ? implode(',', $missingIssues) : 'None'));
        return $missingIssues;
    }
}
