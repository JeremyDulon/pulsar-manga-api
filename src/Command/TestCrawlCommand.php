<?php

namespace App\Command;

use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\MangaPlatform\Platforms\MangaSeePlatform;
use App\MangaPlatform\Platforms\TCBScansPlatform;
use App\Service\CrawlService;
use App\Service\ImageService;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

/**
 * @deprecated A JOUR ?
 */
class TestCrawlCommand extends BaseCommand
{
    public static $defaultName = 'pm:crawl';

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
        $this->crawlMangaSee();
        return 0;
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
        $this->crawler->openUrl('https://mangasee123.com/manga/One-Piece', [
//        $this->crawler->openUrl('https://tcbscans-manga.com/manga/one-punch-man/chapter-203/', [
            'domain' => $platform->getDomain(),
            'baseUrl' => $platform->getBaseUrl(),
            'cookies' => $platform->getCookies()
        ]);

        dump([
            'title' => $this->crawler->findNode($platform->getTitleNode()),
            'author' => $this->crawler->findNode($platform->getAuthorNode()),
            'status' => $this->crawler->findNode($platform->getStatusNode()),
            'img' => $this->crawler->findNode($platform->getMainImageNode()),
//            'issues' => count($this->crawler->findNode($platform->getComicPagesNode()))
        ]);

        $this->crawler->closeClient();
    }

    protected function crawlMangaFox()
    {
        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox"
        ];

        $options = [
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
        ];

        $client = Client::createChromeClient(null, $args, $options);
        $client->request('GET', 'https://fanfox.net/manga/boku_no_hero_academia/v00/c000/1.html');

    }

    protected function test() {
        dump('beforeClient');

        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox",
            "--disable-dev-shm-usage",
            '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.88 Safari/537.36', // Avoir obligatoirement un user agent !!!
        ];;

        $options = [
            'port' => mt_rand(9500, 9600),
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
//            'capabilities' => [
//                ChromeOptions::CAPABILITY => $chromeOptions
//            ]
        ];

        $client = Client::createChromeClient(null, $args, $options);
        dump('beforeRequest');
        $client->request('GET', 'https://fanfox.net/manga/boku_no_hero_academia/v00/c000/1.html');
//        $crawler = $client->waitFor('#viewer');
//        dump($client);
        dump('afterRequest');



        $client->executeScript("
            var mkey = '';
            if ($('#dm5_key').length > 0) {
                mkey = $('#dm5_key').val();
            }
            window.pages = [];
            window.ajaxDone = false;
            $.ajax({
                url: 'chapterfun.ashx',
                data: { cid: chapterid, page: 1, key: '' },
                type: 'GET',
                error: function (msg) {
                },
                success: function (msg) {
                    if (msg != '') {
                        var arr;
                        eval(msg);
                        window.pages = d;
                        window.ajaxDone = true;
                    }
                }
            });
        ");
        $client->getWebDriver()->wait()->until(ajaxChapter());
        $pages = $client->executeScript("return window.pages;");

        dump($pages);
        $client->executeScript("
            window.pages = [];
            window.ajaxDone = false;
            $.ajax({
                url: 'chapterfun.ashx',
                data: { cid: chapterid, page: 2, key: mkey },
                type: 'GET',
                error: function (msg) {
                },
                success: function (msg) {
                    if (msg != '') {
                        var arr;
                        eval(msg);
                        window.pages = d;
                        window.ajaxDone = true;
                    }
                }
            });
        ");
        $client->getWebDriver()->wait()->until(ajaxChapter());
        $pages = $client->executeScript("return window.pages;");
        dump($chapterId);
        dump($pages);
//        foreach ($pages as $page) {
//            $url = 'https:' . $page;
//            $image = $this->imageService->uploadChapterImage($url);
//            dump($image->getExternalUrl());
//        }
    }
}
