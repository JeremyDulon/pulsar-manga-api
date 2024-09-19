<?php

namespace App\Command;

use App\Entity\ComicLanguage;
use App\Entity\ComicPlatform;
use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\MangaPlatform\Platforms\MangaSeePlatform;
use App\MangaPlatform\Platforms\TCBScansPlatform;
use App\Service\CrawlService;
use App\Service\ImageService;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCrawlCommand extends BaseCommand
{
    public static $defaultName = 'mk:crawl:test';

    /** @var ImportService $importService */
    protected $importService;

    /** @var ImageService $imageService */
    protected $imageService;

    protected $crawler;

    public function __construct(
        EntityManagerInterface $em,
        ImportService $importService,
        ImageService $imageService,
        CrawlService $crawler
    )
    {
        parent::__construct($em);

        $this->importService = $importService;
        $this->imageService = $imageService;
        $this->crawler = $crawler;
    }

    protected function configure()
    {
        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

//        $this->crawlTSBScans();
//        $this->crawlMangaSee();

        $this->testPlatformWeight();

        return 0;
    }

    protected function testPlatformWeight(): void
    {
        $slug = 'one-piece';
        $language = 'EN';
        /** @var ComicLanguage $comicLanguage */
        $comicLanguage = $this->em->getRepository(ComicLanguage::class)->findOneBySlugAndLanguage($slug, $language);
        /** @var ComicPlatform $comicPlatform */
        foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
            dump([
                'name' => $comicPlatform->getPlatform()->getName(),
                'weight' => $comicPlatform->getWeight()
            ]);
        }
    }

    protected function crawlTSBScans(): void
    {
        $platform = new TCBScansPlatform();
//        $this->crawler->openUrl('https://tcbscans-manga.com/manga/one-punch-man/', [
        $this->crawler->openUrl('https://tcbscans-manga.com/manga/one-punch-man/chapter-203/', [
            'domain' => $platform->getDomain(),
            'baseUrl' => $platform->getBaseUrl(),
            'cookies' => $platform->getCookies()
        ]);

        $pages = $this->crawler->findNode($platform->getComicPagesNode());
        dump($pages);

        $this->crawler->closeClient();
    }

    protected function crawlMangaSee()
    {
        $platform = new MangaSeePlatform();
//        $this->crawler->openUrl('https://mangasee123.com/manga/One-Piece', [
        $this->crawler->openUrl('https://mangasee123.com/manga/Dragon-Ball', [
            'domain' => $platform->getDomain(),
            'baseUrl' => $platform->getBaseUrl(),
            'cookies' => $platform->getCookies()
        ]);

        $issues = $this->crawler->findNode($platform->getComicIssuesDataNode());

        dump([
            'title' => $this->crawler->findNode($platform->getTitleNode()),
            'author' => $this->crawler->findNode($platform->getAuthorNode()),
            'status' => $this->crawler->findNode($platform->getStatusNode()),
            'img' => $this->crawler->findNode($platform->getMainImageNode()),
            'description' => $this->crawler->findNode($platform->getDescriptionNode()),
            'issues' => count($issues),
            'firstIssue' => $issues[0] ?? null
        ]);

        $this->crawler->openUrl($issues[0]['url'] ?? '', [
            'domain' => $platform->getDomain(),
            'baseUrl' => $platform->getBaseUrl(),
            'cookies' => $platform->getCookies()
        ]);

        $issuePages = $this->crawler->findNode($platform->getComicPagesNode());

        dump([
            'pages' => $issuePages
        ]);

        $this->crawler->closeClient();
    }
}
