<?php

namespace App\Command;

use App\Entity\Platform;
use App\MangaPlatform\Platforms\FanFoxPlatform;
use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\Service\CrawlService;
use App\Service\ImageService;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

/**
 * @deprecated A JOUR ?
 */
class ControlCrawlCommand extends BaseCommand
{
    public static $defaultName = 'pm:crawl:control';

    /** @var CrawlService $crawlService */
    protected $crawlService;

    public function __construct(
        EntityManagerInterface $em,
        CrawlService $crawlService
    )
    {
        parent::__construct($em);

        $this->crawlService = $crawlService;
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

        $this->controlFindNode();
        return 0;
    }

    private function controlFindNode() {
        // MANGA PARK
//        $this->crawlService->openUrl('https://mangapark.net/comic/10016/naruto');
//
//        $platform = new MangaParkPlatform();
//        dump([
//            'title' => $this->crawlService->findNode($platform->getTitleNode()),
//            'altTitles' => $this->crawlService->findNode($platform->getAltTitlesNode()),
//            'status' => $this->crawlService->findNode($platform->getStatusNode()),
//        ]);
//        $this->crawlService->closeClient();


        // FANFOX
//        $this->crawlService->openUrl('https://fanfox.net/manga/boku_no_hero_academia/');
        $platform = new FanFoxPlatform();
        $this->crawlService->openUrl('https://fanfox.net/manga/berserk/', [
            'domain' => $platform->getDomain(),
            'baseUrl' => $platform->getBaseUrl(),
            'cookies' => $platform->getCookies()
        ]);

        $platform = new FanFoxPlatform();
        dump([
            'title' => $this->crawlService->findNode($platform->getTitleNode()),
            'author' => $this->crawlService->findNode($platform->getAuthorNode()),
            'status' => $this->crawlService->findNode($platform->getStatusNode()),
            'issues' => $this->crawlService->findNode($platform->getComicIssuesDataNode(), ['offset' => 2, 'chapterNumber' => 0])

        ]);
        $this->crawlService->closeClient();
    }
}
