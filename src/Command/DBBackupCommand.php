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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMangaCommand extends BaseCommand
{
    public static $defaultName = 'pm:import:manga';

    protected $mangaDom;
    protected $chapterDom;

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

        $this->addOption(
            'url',
            'u',
            InputOption::VALUE_REQUIRED,
            'The url of the manga or chapter you want to import'
        );
        $this->addOption(
            'images',
            'i',
            InputOption::VALUE_NONE,
            'If you want to add chapter images or not.'
        );
        $this->addOption(
            'offset',
                'o',
            InputOption::VALUE_REQUIRED,
            'The number of chapters from the start',
            0
        );
        $this->addOption(
            'chapter',
            'c',
            InputOption::VALUE_REQUIRED,
            'The number of the chapter you want to start from',
            null
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
        $this->stopwatch->start('manga');

        $url = $this->input->getOption('url');
        $offset = $this->input->getOption('offset');
        $chapter = $this->input->getOption('chapter');
        $addImages = $this->input->getOption('images');

        if ($url) {
            $platformUrlInfo = UtilsPlatform::checkUrl($url);
            if (!empty($platformUrlInfo)) {
                $mangaPlatform = $this->importService->importManga($url, $platformUrlInfo['manga'], $offset, $chapter, $addImages);

                $stopEvent = (string) $this->stopwatch->stop('manga');
                $title = $mangaPlatform->getManga()->getTitle();

                $this->output->writeln("Manga updated: $title - $stopEvent");
            }
        }
        return 0;
    }
}
