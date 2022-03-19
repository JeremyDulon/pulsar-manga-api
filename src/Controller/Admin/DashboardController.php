<?php

namespace App\Controller\Admin;

use App\Entity\Comic;
use App\Entity\ComicIssue;
use App\Entity\ComicPlatform;
use App\Entity\Platform;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use JMS\JobQueueBundle\Entity\Job;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pulsar Comics');
    }

    public function configureMenuItems(): iterable
    {
        $submenu1 = [
            MenuItem::linkToCrud('Comics', 'fas fa-book', Comic::class),
            MenuItem::linkToCrud('Comic Platforms', 'fas fa-book', ComicPlatform::class),
            MenuItem::linkToCrud('Comic Issues', 'fas fa-book-open', ComicIssue::class),
            MenuItem::linkToCrud('Platforms', 'fas fa-server', Platform::class),
        ];

        $submenu2 = [
            MenuItem::linkToCrud('JMS Jobs', 'fas fa-wrench', Job::class),
        ];

        $submenu3 = [
            MenuItem::linkToCrud('User', 'fas fa-wrench', User::class),
        ];

        yield MenuItem::subMenu('Comic', 'fas fa-book')->setSubItems($submenu1);
        yield MenuItem::subMenu('Jobs', 'fas fa-wrench')->setSubItems($submenu2);
        yield MenuItem::subMenu('User', 'fas fa-user')->setSubItems($submenu3);
    }
}
