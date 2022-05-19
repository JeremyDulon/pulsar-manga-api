<?php

namespace App\Command;

use App\Entity\Comic;
use App\Entity\Platform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class ImportCommand extends BaseCommand
{
    public static $defaultName = 'pm:import';

    /** @var ImportService $importService */
    protected $importService;

    protected $logger;

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
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->stopwatch->start('manga');

        $comicSlug = $this->input->getArgument('slug');
        $language = $this->input->getArgument('language');

        $this->importService->importComic($comicSlug, $language);

        $eventInfo = $this->stopEvent('manga');

        $this->output->writeln("Manga updated: $comicSlug ($language) - $eventInfo");

        return 0;
    }
}
