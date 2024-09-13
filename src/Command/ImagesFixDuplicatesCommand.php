<?php

namespace App\Command;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicLanguage;
use App\Entity\ComicPage;
use App\Entity\Platform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
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
class ImagesFixDuplicatesCommand extends BaseCommand
{
    public static $defaultName = 'pm:images:fix-duplicates';

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
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $comicPages = $this->em->getRepository(ComicPage::class)
            ->findByComicSlugAndLanguage('one-piece', 'EN');

        $output->writeln('How much One Piece pages: ' . count($comicPages));

        $endPageUrl = 'https://cdn.manga.lykaos.fr/issues/54206efe40be1ba8eba19622956b1192.jpg';

        $endPageContent = file_get_contents($endPageUrl);
        $endPageContentMD5 = md5($endPageContent);

        $imageContent = file_get_contents('https://cdn.manga.lykaos.fr/issues/4a7c05f8421c7fa874da30fed2fdf89d.jpg');
        $imageContentMD5 = md5($imageContent);

        dump($endPageContentMD5);
        dump($imageContentMD5);
        die;

        /** @var ComicPage $comicPage */
        foreach ($comicPages as $comicPage) {
            $imageUrl = $comicPage->getFile()->getExternalUrl();

            $imageContent = file_get_contents($imageUrl);
            $imageContentMD5 = md5($imageContent);
        }

        return 0;
    }
}
