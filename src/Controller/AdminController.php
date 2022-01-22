<?php

namespace App\Controller;

use App\Command\ImportMangaCommand;
use App\Entity\Manga;
use App\Entity\ComicPlatform;
use App\Service\ConsoleService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends EasyAdminController
{
    // Remake: this
    /**
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function importAction(): RedirectResponse
    {
        $id = $this->request->query->get('id');
        /** @var Manga $entity */
        $entity = $this->em->getRepository(MangaLanguage::class)->find($id);

        $options = [
            '--images',
            '--url=' . $entity->getSourceUrl(),
            '--offset=10',
            '--chapter=' . $entity->getLatestChapter()->getNumber()
        ];

        $cronJob = ConsoleService::createConsoleJob(
            ImportMangaCommand::$defaultName,
            $options
        );

        $this->em->persist($cronJob);
        $this->em->flush();

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }
}
