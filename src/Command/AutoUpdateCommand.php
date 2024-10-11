<?php

namespace App\Command;

use App\Entity\ComicLanguage;
use App\Entity\ComicPlatform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Importe les x prochains chapitres de chaque comic en autoupdate=true
 */
class AutoUpdateCommand extends BaseCommand
{
    // TODO: Remake this
    public static $defaultName = 'mk:import:autoupdate';

    protected ImportService $importService;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ImportService $importService, LoggerInterface $logger)
    {
        parent::__construct($em);

        $this->logger = $logger;
        $this->importService = $importService;
    }

    protected function configure()
    {
        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $comicLanguages = $this->em->getRepository(ComicLanguage::class)
            ->findBy([
                'autoUpdate' => true
            ]);

        /** @var ComicLanguage $comicLanguage */
        foreach ($comicLanguages as $comicLanguage) {
            /** @var ComicPlatform $comicPlatform */
            foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
                $this->importService->setLimit(5);
                $this->importService->setStartingNumber($comicLanguage->getLatestComicIssue()->getNumber() + 1);
                $this->importService->importComicIssues($comicPlatform);
            }
        }

        // TODO: Send notifs

        return 0;
    }
}
