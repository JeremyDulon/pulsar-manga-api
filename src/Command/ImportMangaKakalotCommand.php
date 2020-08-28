<?php

namespace App\Command;

use App\Entity\Chapter;
use App\Entity\ChapterPage;
use App\Entity\File;
use App\Entity\Manga;
use App\Service\ImageHelper;
use App\Utils\Functions;
use App\Utils\Platform;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMangaKakalotCommand extends BaseCommand
{
    protected static $defaultName = 'pm:import:manga-kakalot';

    protected const MANGA_DATE_FORMAT = 'M d,Y - H:i A';
    protected const CHAPTER_DATE_FORMAT = 'M d,Y H:i';

    protected $uploadService;

    public function __construct(EntityManagerInterface $em, ImageHelper $uploadService)
    {
        parent::__construct($em);

        $this->uploadService = $uploadService;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->stopwatch->start('manga');

        $mangadom = new Dom();
        // todo prompt
//        $mangadom->loadFromUrl('https://manganelo.com/manga/read_one_piece_manga_online_free4');
//        $mangadom->loadFromUrl('https://manganelo.com/manga/tv923738');
        $mangadom->loadFromUrl('https://manganelo.com/manga/dragon_ball_super');

        $title = $mangadom->find('.story-info-right h1', 0);
        if ($title) {
            $mangaTitle = $title->text;
            $slug = Functions::slugify($mangaTitle);
            $manga = $this->em->getRepository(Manga::class)->findOneBy([
                'slug' => $slug
            ]);

            $statusNode = $mangadom->find('.variations-tableInfo tbody tr .table-value', 2);
            $status = $statusNode->text === 'Ongoing' ? Manga::STATUS_ONGOING : Manga::STATUS_ENDED;

            if (!$manga) {
                $manga = new Manga();
                $manga
                    ->setStatus($status)
                    ->setSlug($slug)
                    ->setTitle($mangaTitle);

                $mangaImageNode = $mangadom->find('.info-image .img-loading', 0);
                $mangaImageUrl = $mangaImageNode->getAttribute('src');
                $mangaImage = $this->uploadService->uploadMangaImage($mangaImageUrl);
                $manga->setImage($mangaImage);

                $this->em->persist($manga);
                $this->em->flush();
            }

            $lastUpdated = $mangadom->find('.story-info-right-extent .stre-value', 0);
            if ($lastUpdated) {
                $manga->setLastUpdated(DateTime::createFromFormat(self::MANGA_DATE_FORMAT,$lastUpdated->text));
            }

            $views = $mangadom->find('.story-info-right-extent .stre-value', 1);
            if ($views) {
                $manga->setViews($views->text);
            }

            // CHAPTERS
            $chaptersLinks = $mangadom->find('ul.row-content-chapter li.a-h');
            foreach ($chaptersLinks as $chapterLink) {
                $chapterUrlNode = $chapterLink->find('a');
                $chapterUrl = $chapterUrlNode->getAttribute('href');
                $chapterTitle = $chapterUrlNode->text;

                $chapterDateNode = $chapterLink->find('.chapter-time');
                $chapterDate = $chapterDateNode->getAttribute('title');

                $chapterNumber = explode('_', basename($chapterUrl))[1];
                $chapter = $this->em->getRepository(Chapter::class)->findOneBy([
                    'number' => $chapterNumber,
                    'manga' => $manga,
                    'platform' => Platform::PLATFORM_KAKALOT
                ]);

                if (!$chapter) {
                    $chapter = new Chapter();
                    $chapter
                        ->setTitle($chapterTitle)
                        ->setNumber($chapterNumber)
                        ->setPlatform(Platform::PLATFORM_KAKALOT)
                        ->setManga($manga);

                    if ($chapterDate) {
                        $chapter->setDate(DateTime::createFromFormat(self::CHAPTER_DATE_FORMAT, $chapterDate));
                    }

                    $this->em->persist($chapter);

                    // CHAPTER IMAGES
                    $chapterDom = new Dom();
                    $chapterDom->loadFromUrl($chapterUrl);

                    $chapter->removeAllChapterPages();

                    $chapterPages = $chapterDom->find('.container-chapter-reader img');
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
            $stopEvent = $this->stopwatch->stop('manga');
            $this->output->writeln("Manga added: $mangaTitle - " . (string) $stopEvent);
        }


        $this->em->flush();
        return 0;
    }
}
