<?php

namespace App\Command;

use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPlatform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Importe les x prochains chapitres de chaque comic en autoupdate=true
 */
class ReimportIssueCommand extends BaseCommand
{
    // TODO: Remake this
    public static $defaultName = 'mk:import:quality-reimport';

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

        $this->addArgument(
            'quality',
            InputArgument::REQUIRED,
            'Minimal quality to reimport'
        );
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
        $quality = $this->input->getArgument('quality');

        $comicIssues = $this->em->getRepository(ComicIssue::class)
            ->findByMinimumQuality($quality);

        $this->importService->setLimit(1);
        /** @var ComicIssue $comicIssue */
        foreach ($comicIssues as $comicIssue) {
            $comicLanguage = $comicIssue->getComicLanguage();
            $this->importService->setStartingNumber($comicIssue->getNumber());

            foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
                $this->importService->importComicIssues($comicPlatform, true);
            }
        }

        // TODO: Send notifs

        return 0;
    }
}
