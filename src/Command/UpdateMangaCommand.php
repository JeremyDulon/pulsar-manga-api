<?php

namespace App\Command;

use App\Entity\ComicPlatform;
use App\Service\ImportService;
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

/**
 * @deprecated A JOUR ?
 */
class UpdateMangaCommand extends BaseCommand
{
    // TODO: Remake this
    public static $defaultName = 'pm:manga:update';

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
            'The source url of the manga you want to update'
        );
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->stopwatch->start('manga');

        $url = $this->input->getOption('url');

        $mangaRepository = $this->em->getRepository(ComicPlatform::class);

        if ($url) {
            $mangaPlatform = $mangaRepository->findOneBy(['sourceUrl' => $url]);
            if ($mangaPlatform !== null) {
                /** @var ComicPlatform $mangaPlatform */
                $this->updateManga($mangaPlatform);
            }
        } else {
            $mangaPlatforms = $mangaRepository->findAll();
            foreach ($mangaPlatforms as $mangaPlatform) {
                /** @var ComicPlatform $mangaPlatform */
                $this->updateManga($mangaPlatform);
            }
        }

        return 0;
    }

    /**
     * @param ComicPlatform $mangaPlatform
     */
    protected function updateManga(ComicPlatform $mangaPlatform) {
        $this->importService->addMangaImage($mangaPlatform);
        $this->importService->fillManga($mangaPlatform);
        $title = $mangaPlatform->getManga()->getTitle();
        $this->output->writeln("Manga updated: $title");
    }
}
