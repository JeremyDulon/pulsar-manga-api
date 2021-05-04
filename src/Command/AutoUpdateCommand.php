<?php

namespace App\Command;

use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoUpdateCommand extends BaseCommand
{
    // Remake: this
    public static $defaultName = 'pm:manga:autoupdate';

    /** @var ImportService $importService */
    protected $importService;

    protected $logger;

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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $mangas = $this->em->getRepository(Manga::class)
            ->findBy([
                'autoUpdate' => true
            ]);

        /** @var MangaPlatform $mangaPlatform */
        foreach ($mangas as $manga) {
            $this->importService->importChapters(
                $manga,
                2,
                $manga->getLatestChapter()->getNumber(),
                true
            );
        }

        // TODO: Send notifs

        return 0;
    }
}
