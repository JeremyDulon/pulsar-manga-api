<?php

namespace App\Command;

use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\Manga;
use App\Entity\MangaPlatform;
use App\Entity\Platform;
use App\Service\ImageHelper;
use App\Service\ImportService;
use App\Utils\Functions;
use App\Utils\Platform as UtilsPlatform;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMangaKakalotCommand extends BaseCommand
{
    protected static $defaultName = 'pm:import:manga';

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
            null,
            InputOption::VALUE_REQUIRED,
            'The url of the manga or chapter you want to import'
        );
        $this->addOption(
            'add-image',
            'ai',
            InputOption::VALUE_NONE,
            'If you want to add chapter images or not.'
        );
        $this->addOption(
            'chapters',
                'c',
            InputOption::VALUE_REQUIRED,
            'The number of chapters you want to get from the manga',
            0 // 0 Means all chapter, -x means x from the end, x means x from the beginning
        );
        // Todo: add other parameters
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
        $chapters = $this->input->getOption('chapters');

        if ($url) {
            $isManga = UtilsPlatform::checkUrl($url);
            if (is_bool($isManga)) {
                if ($isManga === true) {
                    $mangaPlatform = $this->importService->importManga($url);

                    $stopEvent = (string) $this->stopwatch->stop('manga');
                    $title = $mangaPlatform->getManga()->getTitle();

                    $this->output->writeln("Manga added: $title - $stopEvent");
                } else {
                    $this->output->writeln("Todo add chapter");
                }
            }
        }

        $this->em->flush();
        return 0;
    }

    /**
     * @param MangaPlatform $mangaPlatform
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ClientExceptionInterface
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function addChapters(MangaPlatform $mangaPlatform) {
        $chaptersLinks = $this->mangaDom->find('ul.row-content-chapter li.a-h');
        foreach ($chaptersLinks as $chapterLink) {
            $chapterUrlNode = $chapterLink->find('a');
            $chapterUrl = $chapterUrlNode->getAttribute('href');
            $chapterTitle = $chapterUrlNode->text;

            $chapterDateNode = $chapterLink->find('.chapter-time');
            $chapterDate = $chapterDateNode->getAttribute('title');

            $chapterNumber = explode('_', basename($chapterUrl))[1];
            $chapter = $this->em->getRepository(Chapter::class)->findOneBy([
                'number' => $chapterNumber,
                'manga' => $mangaPlatform,
            ]);

            if (!$chapter) {
                $chapter = new Chapter();
                $chapter
                    ->setTitle($chapterTitle)
                    ->setNumber($chapterNumber)
                    ->setManga($mangaPlatform);

                if ($chapterDate) {
                    $chapter->setDate(DateTime::createFromFormat(self::CHAPTER_DATE_FORMAT, $chapterDate));
                }

                $this->em->persist($chapter);

                // CHAPTER IMAGES
                $this->chapterDom = new Dom();
                $this->chapterDom->loadFromUrl($chapterUrl);

                $chapter->removeAllChapterPages();

                $chapterPages = $this->chapterDom->find('.container-chapter-reader img');
                $pageNumber = 1;
                foreach ($chapterPages as $page) {
                    $imageUrl = $page->getAttribute('src');
                    $file = $this->uploadService->uploadChapterImage($imageUrl, [
                        'Referer: ' . $chapterUrl
                    ]);

                    $chapterPage = new ChapterPage();
                    $chapterPage
                        ->setChapter($chapter)
                        ->setNumber($pageNumber)
                        ->setFile($file);

                    $this->em->persist($chapterPage);
                    $pageNumber++;
                }
                $this->em->flush();

                $this->output->writeln("New chapter chapter added: $chapterNumber");
            } else {
                $this->output->writeln("Chapter already added: $chapterNumber");
            }
        }
    }
}
