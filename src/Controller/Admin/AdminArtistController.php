<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/artist')]
final class AdminArtistController extends AbstractController
{
    #[Route(name: 'admin_artist_index', methods: ['GET'])]
    public function index(ArtistRepository $artistRepository): Response
    {
        return $this->render('admin/artist/index.html.twig', [
            'artists' => $artistRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_artist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $artist = new Artist();
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($artist);
            $entityManager->flush();

            return $this->redirectToRoute('admin_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/artist/new.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_artist_show', methods: ['GET'])]
    public function show(Artist $artist): Response
    {
        return $this->render('admin/artist/show.html.twig', [
            'artist' => $artist,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_artist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artist $artist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_artist_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/artist/edit.html.twig', [
            'artist' => $artist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_artist_delete', methods: ['POST'])]
    public function delete(Request $request, Artist $artist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artist->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($artist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_artist_index', [], Response::HTTP_SEE_OTHER);
    }
}
