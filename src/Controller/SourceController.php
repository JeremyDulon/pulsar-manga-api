<?php


namespace App\Controller;

use App\Command\ImportMangaCommand;
use App\Service\ConsoleService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
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

            $job = ConsoleService::createConsoleJob(
                ImportMangaCommand::$defaultName,
                $options
            );

            $this->em->persist($job);
            $this->em->flush();

            return true;
        }

        return false;
    }
}
