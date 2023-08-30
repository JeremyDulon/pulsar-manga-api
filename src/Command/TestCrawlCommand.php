<?php

namespace App\Command;

use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\Service\ImageService;
use App\Service\ImportService;
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

    public function __construct(EntityManagerInterface $em, ImportService $importService, ImageService $imageService)
    {
        parent::__construct($em);

        $this->importService = $importService;
        $this->imageService = $imageService;
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

        $this->test();
        return 0;
    }

    protected function test() {
        dump('beforeClient');

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
