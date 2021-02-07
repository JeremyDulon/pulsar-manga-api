<?php

namespace App\Command;

use App\MangaPlatform\Platform\MangaParkPlatform;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class TestCrawlCommand extends BaseCommand
{
    public static $defaultName = 'pm:crawl';

    /** @var ImportService $importService */
    protected $importService;

    public function __construct(EntityManagerInterface $em, ImportService $importService)
    {
        parent::__construct($em);

        $this->importService = $importService;
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

        $platform = new MangaParkPlatform();

        dump($platform->getTitleNode());
        return 0;
    }

    protected function test() {
        dump('beforeClient');

        $client = Client::createChromeClient();
//        $client->request('GET', 'https://mangafast.net/read/boruto-naruto-next-generations-english/');
        dump('beforeRequest');
//        $client->request('GET', 'https://mangapark.net/manga/dragon-ball-super-toyotarou/i2640593/c068');
        $client->request('GET', 'https://www.mangahere.cc/manga/dragon_ball_super/c001/1.html');
//        $crawler = $client->waitFor('#viewer');
//        dump($client);
        dump('afterRequest');

        $pages = $client->executeScript('return _load_pages;');
        dump(count($pages));
    }
}
