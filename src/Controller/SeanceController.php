<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Form\SeanceType;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class SeanceController extends AbstractController
{
    #[Route('/seances', name: 'afficher_seances')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $seances = $entityManager->getRepository(Seance::class)->findAll();

        return $this->render('seance/index.html.twig', [
            'seances' => $seances,
        ]);
    }
    #[Route('/dash', name: 'afficher_dash')]
    public function indexdash(EntityManagerInterface $entityManager): Response
    {
        $seances = $entityManager->getRepository(Seance::class)->findAll();

        return $this->render('dash.html.twig', [
            'seances' => $seances,
        ]);
    }

    #[Route('/seances/cours/{id}', name: 'seances_par_cours', methods: ['GET'])]
    public function getSeancesParCours(
        int $id, 
        SeanceRepository $seanceRepository, 
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $seances = $seanceRepository->findBy(['cour' => $id]);
    
        $data = array_map(function (Seance $seance) use ($csrfTokenManager) {
            return [
                'id' => $seance->getId(),
                'date' => $seance->getDate()->format('d/m/Y'),
                'professeur' => $seance->getProfesseur(),
                'csrf_token' => $csrfTokenManager->getToken('delete' . $seance->getId())->getValue(),
                'title' => $seance->getProfesseur() . ' - ' . $seance->getDate()->format('d/m/Y'),
                'start' => $seance->getDate()->format('Y-m-d\TH:i:s'),  // format approprié pour FullCalendar
                'end' => $seance->getDate()->add(new \DateInterval('PT1H'))->format('Y-m-d\TH:i:s') // Ajout d'une durée d'une heure pour l'exemple
            ];
        }, $seances);
    
        return $this->json($data);
    }

    #[Route('/seance/ajouter', name: 'ajouter_seance')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $seance = new Seance();
        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($seance);
            $entityManager->flush();
            return $this->redirectToRoute('afficher_seances');
        }

        return $this->render('seance/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/seance/modifier/{id}', name: 'modifier_seance')]
    public function modifier(Seance $seance, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('afficher_seances');
        }

        return $this->render('seance/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/seance/supprimer/{id}', name: 'supprimer_seance', methods: ['POST'])]
    public function supprimer(int $id, EntityManagerInterface $entityManager): Response
    {
        $seance = $entityManager->getRepository(Seance::class)->find($id);

        if ($seance) {
            $entityManager->remove($seance);
            $entityManager->flush();
        }

        return $this->redirectToRoute('afficher_seances');
    }
}
