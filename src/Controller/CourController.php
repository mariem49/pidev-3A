<?php

namespace App\Controller;

use App\Entity\Cour;
use App\Form\CourType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourController extends AbstractController
{
    #[Route('/cours', name: 'afficher_cours')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cours = $entityManager->getRepository(Cour::class)->findAll();

        return $this->render('cour/index.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/cour/ajouter', name: 'ajouter_cour')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cour = new Cour();
        $form = $this->createForm(CourType::class, $cour);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cour);
            $entityManager->flush();

            // Ajout d'un message flash après l'ajout du cours
            $this->addFlash('success', 'Le cours a été ajouté avec succès!');

            return $this->redirectToRoute('afficher_cours');
        }

        return $this->render('cour/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cour/modifier/{id}', name: 'modifier_cour')]
    public function modifier(Cour $cour, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourType::class, $cour);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Ajout d'un message flash après la modification du cours
            $this->addFlash('success', 'Le cours a été modifié avec succès!');

            return $this->redirectToRoute('afficher_cours');
        }

        return $this->render('cour/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/cour/supprimer/{id}', name: 'supprimer_cour', methods: ['POST'])]
    public function supprimer(Cour $cour, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();

            // Ajout d'un message flash après la suppression du cours
            $this->addFlash('success', 'Le cours a été supprimé avec succès!');
        }

        return $this->redirectToRoute('afficher_cours');
    }
}
