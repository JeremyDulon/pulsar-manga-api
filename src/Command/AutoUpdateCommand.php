<?php

namespace App\Command;

use App\Entity\ComicLanguage;
use App\Entity\ComicPlatform;
use App\Service\ImportService;
use App\Utils\PlatformUtil;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

/*
 * Importe les x prochains chapitres de chaque comic en autoupdate=true
 */
class AutoUpdateCommand extends BaseCommand
{
    // TODO: Remake this
    public static $defaultName = 'mk:import:autoupdate';

    protected ImportService $importService;

    protected LoggerInterface $logger;

    protected MailerInterface $mailer;

    private array $issuesImported = [];

    public function __construct(EntityManagerInterface $em, ImportService $importService, LoggerInterface $logger, MailerInterface $mailer)
    {
        parent::__construct($em);

        $this->logger = $logger;
        $this->importService = $importService;
        $this->mailer = $mailer;
    }

    protected function configure()
    {
        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $comicLanguages = $this->em->getRepository(ComicLanguage::class)
            ->findBy([
                'autoUpdate' => true
            ]);

        /** @var ComicLanguage $comicLanguage */
        foreach ($comicLanguages as $comicLanguage) {
            /** @var ComicPlatform $comicPlatform */
            foreach ($comicLanguage->getComicPlatforms() as $comicPlatform) {
                $this->importService->setLimit(5);
                $startingNumber = 1;
                if ($comicLanguage->getLatestComicIssue() !== null) {
                    $startingNumber = $comicLanguage->getLatestComicIssue()->getNumber() + 1;
                }
                $this->importService->setStartingNumber($startingNumber);
                $this->importService->importComicIssues($comicPlatform);
            }

            $comicSlug = $comicLanguage->getComic()->getSlug();
            if (count($this->importService->getIssuesImported()) > 0) {
                if (array_key_exists($comicSlug, $this->issuesImported) === false) {
                    $this->issuesImported[$comicSlug] = [
                        'name' => $comicLanguage->getComic()->getTitle(),
                        'issues' => []
                    ];
                }

                array_push($this->issuesImported[$comicSlug]['issues'], $this->importService->getIssuesImported());
            }
        }

        // TODO: Send notifs
        if (count($this->issuesImported) > 0) {
            $this->sendNotification();
        }

        return 0;
    }

    private function sendNotification(): void
    {
        $email = (new TemplatedEmail())
            ->from('j.dulon.64@gmail.com')
            ->to('jeremy.dulon@live.fr')
            ->subject('MangaKaos: Nouveaux chapitres sortis !')
            ->htmlTemplate('email/newChapters.html.twig')
            ->context([
                'data' => $this->issuesImported
            ]);

        $this->mailer->send($email);
    }
}
