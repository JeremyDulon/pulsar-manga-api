<?php

namespace App\Command;

use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\Service\ImageService;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class ControlCrawlCommand extends BaseCommand
{
    public static $defaultName = 'pm:crawl:demo';

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

        $this->testMangaPark();
        $this->testFanFox();
        return 0;
    }

    protected function testMangaPark() {
        $args = [
            "--headless",
            "--disable-gpu",
            "--no-sandbox",
            '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.88 Safari/537.36', // Avoir obligatoirement un user agent !!!
        ];

        $options = [
            'connection_timeout_in_ms' => 60000,
            'request_timeout_in_ms' => 60000,
        ];

        $client = Client::createChromeClient(null, $args, $options);
        dump('beforeRequest');

        $client->request('GET', 'https://mangapark.net/comic/10016/naruto');
        try {
            $client->waitFor('#mainer');
            $text = $client->getCrawler()->filter('#mainer > div > div.pb-2.alias-set.line-b-f')->getText();
            dump($text);
        } catch (TimeoutException $e) {
            dump($e->getMessage());
            dump($client->getCrawler()->text());
        }

    }

    protected function testFanFox() {
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
        $client->request('GET', 'https://fanfox.net/manga/boku_no_hero_academia/');

        try {
            $client->waitFor('.detail-info-right-title-font');
            $text = $client->getCrawler()->filter('.detail-info-right-title-font')->getText();
            dump($text);
        } catch (TimeoutException $e) {
            dump($e->getMessage());
            dump($client->getCrawler()->text());
        }

    }
}
