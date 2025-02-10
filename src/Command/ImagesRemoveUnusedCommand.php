<?php

namespace App\Command;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPage;
use App\Entity\File;
use App\Entity\Platform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Gaufrette\Filesystem;
use Imagick;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

/**
 * @todo Faire de la comparaison avec les pages de fin
 */
class ImagesRemoveUnusedCommand extends BaseCommand
{
    public static $defaultName = 'pm:images:remove-unused';

    protected $logger;

    protected $filesystem;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Filesystem $filesystem
    )
    {
        parent::__construct($em);

        $this->logger = $logger;
        $this->filesystem = $filesystem;
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

        $dbFiles = $this->em->getRepository(File::class)->findAll();
        $output->writeln(count($dbFiles) . ' files on DB');

        $adapter = $this->filesystem->getAdapter();
        $adapterFiles = array_filter($adapter->keys(), function ($file) {
            return $file;
        });
        $output->writeln(count($adapterFiles) . ' files on FS');

        return 0;
    }
}
