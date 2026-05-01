<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Entity\ArtistRequest;
use App\Entity\Category;
use App\Entity\Link;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}


    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(ArtistRequestCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Her Frequencies');
    }


        public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Artistes');
        yield MenuItem::linkToCrud('Artistes', 'fas fa-music', Artist::class);
        yield MenuItem::linkToCrud('Demandes', 'fas fa-inbox', ArtistRequest::class);

        yield MenuItem::section('Paramètres');
        yield MenuItem::linkToCrud('Catégories', 'fas fa-tags', Category::class);
        yield MenuItem::linkToCrud('Liens', 'fas fa-link', Link::class);
    }

}
