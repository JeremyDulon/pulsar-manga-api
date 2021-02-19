<?php


namespace App\Controller;

use App\Command\ImportMangaCommand;
use App\Service\ConsoleService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class SourceController extends BaseController
{
    /**
     * @Rest\Post("/source/add", name="add_source")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @param Request $request
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addSourceAction(Request $request): bool {
        $url = $request->get('url');
        $offset = $request->get('offset');
        $images = $request->get('images', true);
        $chapter = $request->get('chapter');

        if ($url) {
            // TODO: Check if url if form one of our platforms

            $options = ["--url=".$url];

            if ($images) {
                $options[] = '--images';
            }

            if ($chapter) {
                $options[] = "--chapter=$chapter";
            }

            if ($offset) {
                $options[] = "--offset=$offset";
            }

            $cronJob = new ScheduledCommand();
            $cronJob->setName('Import ' . $url);
            $cronJob->setCronExpression('@daily');
            $cronJob->setPriority(1);
            $cronJob->setDisabled(false);
            $cronJob->setCommand(ImportMangaCommand::$defaultName);
            $cronJob->setArguments(join(' ', $options));
            $cronJob->setExecuteImmediately(true);
            $cronJob->setLogFile('import.log');

            $this->em->persist($cronJob);
            $this->em->flush();

            return true;
        }

        return false;
    }
}
