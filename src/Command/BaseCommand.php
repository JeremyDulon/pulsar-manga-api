<?php


namespace App\Command;

use App\Utils\Functions;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class BaseCommand extends Command
{
    /** @var EntityManagerInterface  */
    protected $em;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var Stopwatch */
    protected $stopwatch;

    /** @var array[] */
    protected $cache = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->stopwatch = new Stopwatch();
    }

    /**
     * @param $eventName
     * @return string
     */
    protected function stopEvent($eventName): string
    {
        $stopEvent = $this->stopwatch->stop($eventName);

        return join(' ', [
            sprintf('%.2F MiB', $stopEvent->getMemory() / 1024 / 1024) .
            Functions::formatMilliseconds($stopEvent->getDuration())
        ]);
    }
}
