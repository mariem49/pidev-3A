<?php

namespace App\Controller;

use App\Entity\Cour;
use App\Form\CourType;
use App\Repository\CourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CourController extends AbstractController
{
    #[Route('/cours', name: 'afficher_cours')]
    public function index(Request $request, CourRepository $courRepository): Response
        {
            // Retrieve search, sort, and order parameters from the query string
            $search = $request->query->get('search', '');
            $sortBy = $request->query->get('sort', 'id');
            $order = $request->query->get('order', 'ASC');
    
            // Pass the search term to the repository to filter results
            $cours = $courRepository->searchAndSort($search, $sortBy, $order);
    
            // Get the course with the most sessions using the new repository method
            $courseWithMostSessions = $courRepository->getCourseWithMostSessions();
    
            return $this->render('cour/index.html.twig', [
                'cours' => $cours,
                'sortBy' => $sortBy,
                'order' => $order,
                'search' => $search,
                'courseWithMostSessions' => $courseWithMostSessions,
            ]);
    }
    

    

    #[Route('/cour/ajouter', name: 'ajouter_cour')]
public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
{
    $cour = new Cour();
    $form = $this->createForm(CourType::class, $cour);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un cours.');
        }

        // Associer l'utilisateur au cours
        $cour->setUser($user);

        // Sauvegarde en base de données
        $entityManager->persist($cour);
        $entityManager->flush();

        // Ajout d'un message flash
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
