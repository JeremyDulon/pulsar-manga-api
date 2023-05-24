<?php

namespace App\Command;

use App\Entity\Comic;
use App\Entity\Platform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

/**
 * Commande principale d'import
 */
class ImportCommand extends BaseCommand
{
    public static $defaultName = 'pm:import';

    protected ImportService $importService;

    protected LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $em,
        ImportService $importService,
        LoggerInterface $logger
    )
    {
        parent::__construct($em);

        $this->logger = $logger;
        $this->importService = $importService;
    }

    protected function configure()
    {
        parent::configure();

        $this->addArgument(
            'slug',
            InputArgument::REQUIRED,
            'The slug of the comic'
        );
        $this->addArgument(
            'language',
            InputArgument::OPTIONAL,
            'The language of the comic',
            PlatformUtil::LANGUAGE_EN
        );
        $this->addArgument(
            'issueNumber',
            InputArgument::OPTIONAL,
            'Issue number to start from',
            null
        );
        $this->addArgument(
            'offset',
            InputArgument::OPTIONAL,
            'Offset to start from',
            0
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
        $this->stopwatch->start('manga');

        $comicSlug = $this->input->getArgument('slug');
        $language = $this->input->getArgument('language');
        $issueNumber = $this->input->getArgument('issueNumber');
        $offset = $this->input->getArgument('offset');

        $executionDetail = $this->importService->importComic($comicSlug, $language, $offset, $issueNumber);

        $eventInfo = $this->stopEvent('manga');

        $comicId = $executionDetail['comic']['id'];
        $comicTitle = $executionDetail['comic']['title'];
        $this->output->writeln("Comic updated: #$comicId $comicTitle ($language) - $eventInfo");
        $detailString = [
            'Detected Issues' => $executionDetail['comic']['issues']['detected'],
            'Added Issues' => $executionDetail['comic']['issues']['added']
        ];
        $this->output->writeln(json_encode($detailString));
        if ($executionDetail['errors'])
        $this->output->writeln("Errors " . json_encode($executionDetail['errors']));

        return 0;
    }
}
