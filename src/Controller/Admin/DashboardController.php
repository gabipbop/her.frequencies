<?php

namespace App\Controller\Admin;

use App\Enum\ArtistRequestStatus;
use App\Repository\ArtistRepository;
use App\Repository\ArtistRequestRepository;
use App\Repository\CategoryRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private ArtistRequestRepository $artistRequestRepository,
        private CategoryRepository $categoryRepository,
    ) {}

    public function index(): Response
    {
        $stats = [
            'approved_artists'   => $this->artistRepository->count([]),
            'pending_requests'   => $this->artistRequestRepository->count(['status' => ArtistRequestStatus::PENDING]),
            'rejected_requests'  => $this->artistRequestRepository->count(['status' => ArtistRequestStatus::REJECTED]),
            'categories'         => $this->categoryRepository->count([]),
        ];

        return $this->render('admin/dashboard.html.twig', ['stats' => $stats]);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addHtmlContentToHead('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Her Frequencies')
            ->setLocales(['en']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Artists');
        yield MenuItem::linkTo(ArtistCrudController::class, 'Artists', 'fas fa-music')->setAction('index');
        yield MenuItem::linkTo(ArtistRequestCrudController::class, 'Pending requests', 'fas fa-inbox')->setAction('index');

        yield MenuItem::section('Settings');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Categories', 'fas fa-tags')->setAction('index');
        yield MenuItem::linkTo(LinkCrudController::class, 'Links', 'fas fa-link')->setAction('index');
    }
}
