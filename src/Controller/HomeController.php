<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        ArtistRepository $artistRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $activeSlug = $request->query->get('category');

        $artists = $activeSlug
            ? $artistRepository->findAcceptedByCategories([$activeSlug])
            : $artistRepository->findAcceptedByCategories();

        $categories = $categoryRepository->findAll();

        return $this->render('home/index.html.twig', [
            'artists'    => $artists,
            'categories' => $categories,
            'activeSlug' => $activeSlug,
        ]);
    }
}
