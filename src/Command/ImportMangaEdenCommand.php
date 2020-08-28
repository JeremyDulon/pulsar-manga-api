<?php

namespace App\Command;

use App\Service\ImageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMangaEdenCommand extends BaseCommand
{
    protected $uploadService;

    protected static $defaultName = 'pm:import:test';

    public function __construct(EntityManagerInterface $em, ImageHelper $uploadService)
    {
        parent::__construct($em);

        $this->uploadService = $uploadService;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $chapterUrl = 'https://manganelo.com/chapter/dragon_ball_super/chapter_62';
        $imageUrl = 'https://s31.mkklcdnv31.com/mangakakalot/d2/dragon_ball_super/chapter_62/1.jpg';

        $this->uploadService->uploadChapterImage($imageUrl, [
            'Referer: ' . $chapterUrl
        ]);

        return 0;
    }
}
