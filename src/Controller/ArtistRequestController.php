<?php
// src/Controller/ArtistRequestController.php
namespace App\Controller;

use App\Entity\ArtistRequest;
use App\Enum\ArtistRequestStatus;
use App\Form\ArtistRequest\Step1Type;
use App\Form\ArtistRequest\Step2Type;
use App\Form\ArtistRequest\Step3Type;
use App\Form\ArtistRequest\Step4Type;
use App\Form\ArtistRequest\Step5Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/artist-request')]
class ArtistRequestController extends AbstractController
{
    private array $steps = [
        1 => Step1Type::class,
        2 => Step2Type::class,
        3 => Step3Type::class,
        4 => Step4Type::class,
        5 => Step5Type::class,
    ];

    #[Route('/', name: 'artist_request_start')]
    public function start(): Response
    {
        return $this->redirectToRoute('artist_request_step', ['step' => 1]);
    }

    #[Route('/step/{step}', name: 'artist_request_step', requirements: ['step' => '[1-5]'])]
    public function step(
        int $step,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $session = $request->getSession();

        // Toujours récupérer depuis la session, jamais depuis la BDD
        $artistRequest = $session->get('artist_request') ?? new ArtistRequest();

        $formType = $this->steps[$step];
        $form = $this->createForm($formType, $artistRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Étape 3 : on gère la photo manuellement
            if ($step === 3) {
                $pictureFile = $form->get('pictureFile')->getData();
                if ($pictureFile) {
                    $newFilename = uniqid() . '.' . $pictureFile->guessExtension();
                    $pictureFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/artist_requests',
                        $newFilename
                    );
                    $session->set('artist_request_picture', $newFilename);
                }
            }

            // On vide le pictureFile avant de mettre en session (pas sérialisable)
            $artistRequest->setPictureFile(null);
            $session->set('artist_request', $artistRequest);

            if ($step === 5) {
                $artistRequest->setPicture($session->get('artist_request_picture'));
                $artistRequest->setStatus(ArtistRequestStatus::PENDING);
                $artistRequest->setCreatedAt(new \DateTimeImmutable());
                $em->persist($artistRequest);
                $em->flush();
                $session->remove('artist_request');
                $session->remove('artist_request_picture');

                return $this->redirectToRoute('artist_request_success');
            }

            return $this->redirectToRoute('artist_request_step', ['step' => $step + 1]);
        }

        return $this->render('artist/step.html.twig', [
            'form' => $form->createView(),
            'step' => $step,
            'totalSteps' => count($this->steps),
        ]);
    }

    #[Route('/success', name: 'artist_request_success')]
    public function success(): Response
    {
        return $this->render('artist_request/success.html.twig');
    }
}
