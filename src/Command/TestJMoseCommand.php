<?php


namespace App\Command;


use App\Service\ImportService;
use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class TestJMoseCommand extends BaseCommand
{
    public static $defaultName = 'pm:cron';


    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
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

        $cron = CronExpression::factory('@hourly');
        $nextRunDate = $cron->getNextRunDate(new \DateTime('1 hour ago'));
        $this->output->writeln('Next Run Date: ' . $nextRunDate->format('d-m-Y H:i:s'));
//        $cronJob = new ScheduledCommand();
//        $cronJob->setName('Insert Berserk');
//        $cronJob->setPriority(1);
//        $cronJob->setDisabled(false);
//        $cronJob->setCommand(ImportMangaCommand::$defaultName);
//        $args = [
//            '--url=https://mangafast.net/read/berserk/',
//            '--images'
//        ];
//        $cronJob->setArguments(join(' ', $args));
//        $cronJob->setExecuteImmediately(true);
//
//        $this->em->persist($cronJob);
//        $this->em->flush();

        return 0;
    }
}
