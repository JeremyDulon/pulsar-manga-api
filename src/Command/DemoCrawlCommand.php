<?php

namespace App\Command;

use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\Service\ImageService;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class DemoCrawlCommand extends BaseCommand
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

        $text = $client->getCrawler()->filter('.reader-header-title-2')->getText();

        dump($text);
    }
}
