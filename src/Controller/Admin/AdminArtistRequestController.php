<?php

namespace App\Controller\Admin;

use App\Entity\ArtistRequest;
use App\Service\ArtistRequestApprover;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminArtistRequestController extends AbstractController
{
    #[Route('/artist-requests/{id}/approve', name: 'admin_artist_request_approve')]
    public function approve(ArtistRequest $request, ArtistRequestApprover $approver): Response
    {
        $approver->approve($request);
        $this->addFlash('success', 'Artist approved !');

        return $this->redirectToRoute('admin_artist_requests');
    }

    #[Route('/artist-requests/{id}/reject', name: 'admin_artist_request_reject')]
    public function reject(ArtistRequest $request, ArtistRequestApprover $approver): Response
    {
        $approver->reject($request);
        $this->addFlash('success', 'Artist rejected !');

        return $this->redirectToRoute('admin_artist_requests');
    }

    #[Route('/artist-requests', name: 'admin_artist_requests', methods: 'GET')]
    public function index(EntityManagerInterface $em): Response
    {
        $artists_requests = $em->getRepository(ArtistRequest::class)->findAll();

        return $this->render('admin/artist_request/index.html.twig', [
            'artists_requests' => $artists_requests,
        ]);
    }
}


