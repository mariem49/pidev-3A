<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    
    #[Route('/map/{numx}/{numy}', name: 'app_evenement_map', methods: ['GET'])]
    public function map(float $numx, float $numy): Response
    {
        return $this->render('evenement/map.html.twig', [
            'x' => $numx,
            'y' => $numy,
        ]);
    }


    
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $validSortByFields = ['titre', 'id']; // Valid fields for sorting
        $sortBy = $request->query->get('sort', 'titre'); // Default to sorting by title
        $order = $request->query->get('order', 'ASC'); // Default to ascending order
        $search = $request->query->get('search', ''); // Get the search parameter
    
        // Validate sorting and order parameters
        if (!in_array($sortBy, $validSortByFields)) {
            $sortBy = 'titre'; // Default to sorting by title
        }
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC'; // Default to ascending order
        }
    
        // If there is a search term, fetch the events matching the search
        if ($search) {
            // Use the repository method to search by name or ID
            $evenements = $evenementRepository->searchByNameAndId($search, $sortBy, $order);
        } else {
            // Fetch all events if no search term is provided
            $evenements = $evenementRepository->findBy([], [$sortBy => $order]);
        }
    
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'sortBy' => $sortBy,
            'order' => $order,
            'search' => $search, // Pass the search parameter to the template
        ]);
    }
    


#[Route('/index', name: 'app_evenement_index_front', methods: ['GET'])]
public function indexFront(Request $request, EvenementRepository $evenementRepository): Response
{
    // Get the sorting parameters from the request or use defaults
    $sortBy = $request->query->get('sort', 'id'); // Default sorting by 'id'
    $order = $request->query->get('order', 'ASC'); // Default sorting order is 'ASC'

    // Make sure the sorting values are valid
    $validSortFields = ['id', 'titre']; // You can add more fields here for sorting
    $validOrder = ['ASC', 'DESC'];

    if (!in_array($sortBy, $validSortFields)) {
        $sortBy = 'id'; // Default to 'id' if the sort value is invalid
    }

    if (!in_array($order, $validOrder)) {
        $order = 'ASC'; // Default to 'ASC' if the order value is invalid
    }

    // Fetch events from the database with sorting applied
    $evenements = $evenementRepository->findBy([], [$sortBy => $order]);

    // Render the view with sorted events and pass sorting variables to the template
    return $this->render('evenement/indexFront.html.twig', [
        'evenements' => $evenements,
        'sortBy' => $sortBy,
        'order' => $order,
    ]);
}

    #[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            // Sauvegarder l'entité
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_show', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/show/{id}', name: 'app_evenement_show_front', methods: ['GET'])]
    public function showFront(Evenement $evenement): Response
    {
        return $this->render('evenement/showFront.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->getPayload()->getString('_token'))) {


            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/search', name: 'app_evenement_search', methods: ['GET'])]
    public function search(Request $request, EvenementRepository $evenementRepository): Response
    {
        $query = $request->query->get('q'); // Récupérer la saisie utilisateur pour la recherche
        $evenements = $evenementRepository->searchByTitleAndId($query); // Recherche par titre et id

        $results = array_map(function (Evenement $evenement) {
            return [
                'id' => $evenement->getId(),
                'titre' => $evenement->getTitre(),
            ];
        }, $evenements);

        return $this->json($results);
    }
}

