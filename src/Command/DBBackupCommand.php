<?php

namespace App\Command;

use App\Service\ImportService;
use App\Utils\Platform as UtilsPlatform;
use Doctrine\ORM\EntityManagerInterface;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\StrictException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DBBackupCommand extends BaseCommand
{
    public static $defaultName = 'pm:db:backup';

    protected static $tables = [
        'user',
        'chapter',
        'chapter_page',
        'manga',
        'manga_platform',
        'platform'
    ];

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'backup',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Backup mode',
            false
        );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->stopwatch->start('backup');
        $backupMode = $input->getOption('backup');

        if ($backupMode !== false) {
            $this->backup();
        } else {
            $this->restore();
        }


        $stopEvent = (string) $this->stopwatch->stop('backup');
        $this->output->writeln("Backup done - $stopEvent");
        return 0;
    }

    protected function backup() {
        $mds = $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($mds as $md) {
            dump($md);
        }
    }

    protected function restore() {

    }
}
