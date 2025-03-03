<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email; 
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

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
public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Paramètres de recherche, de tri et de pagination
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');

        // Création de la requête via QueryBuilder
        $queryBuilder = $entityManager->getRepository(Evenement::class)->createQueryBuilder('e')
            ->where('e.titre LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('e.' . $sort, $order);

        // Pagination : calcul du décalage et de la limite
        $page = $request->query->getInt('page', 1);  // Par défaut, la page 1
        $limit = 4;  // Nombre d'éléments par page
        $offset = ($page - 1) * $limit;

        // Appliquer la pagination à la requête
        $queryBuilder->setFirstResult($offset)
                     ->setMaxResults($limit);

        // Créer un Paginator avec le QueryBuilder
        $paginator = new Paginator($queryBuilder);

        // Récupérer les résultats
        $evenements = $paginator->getQuery()->getResult();

        // Nombre total d'éléments
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Rendu de la vue avec les données nécessaires à la pagination
        return $this->render('evenement/index.html.twig', [
            'evenements' => $evenements,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'search' => $search,
            'sortBy' => $sort,
            'order' => $order,
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

private $mailer;
private $userRepository;
private EvenementRepository $evenementRepository; 


public function __construct(MailerInterface $mailer, UserRepository $userRepository, EvenementRepository $evenementRepository)
{
    $this->mailer = $mailer;
    $this->userRepository = $userRepository;
    $this->evenementRepository = $evenementRepository;

}

#[Route('/new', name: 'app_evenement_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $evenement = new Evenement();
    $form = $this->createForm(EvenementType::class, $evenement);
    $form->handleRequest($request);

    // Récupérer les 4 derniers événements pour les suggestions
    $evenements = $this->evenementRepository->findBy(
        [], // Pas de filtre spécifique
        ['date_debut' => 'DESC'], // Trier par date_debut décroissante
        4 // Limiter à 4 résultats
    );

    $titres = [];
    $descriptions = [];

    // Extraire les titres et descriptions des 4 derniers événements
    foreach ($evenements as $ev) {
        $titres[] = $ev->getTitre();
        $descriptions[] = $ev->getDescription();
    }

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder l'entité
        $entityManager->persist($evenement);
        $entityManager->flush();

        // Récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();

        // Créer l'e-mail à envoyer
        $email = (new Email())
            ->from('no-reply@votreplateforme.com')
            ->subject('Nouveau événement ajouté')
            ->html('<p>Un nouvel événement a été ajouté à la plateforme. Découvrez-le dès maintenant!</p>');

        // Ajouter les destinataires à l'email
        foreach ($users as $user) {
            // Envoie l'email à chaque utilisateur
            $this->mailer->send($email->to($user->getEmail()));
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('evenement/new.html.twig', [
        'evenement' => $evenement,
        'form' => $form,
        'titres' => $titres, // Passer les titres à la vue
        'descriptions' => $descriptions, // Passer les descriptions à la vue
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

    // Récupérer les 4 derniers événements pour les suggestions
    $evenements = $this->evenementRepository->findBy(
        [], // Pas de filtre spécifique
        ['date_debut' => 'DESC'], // Trier par date_debut décroissante
        4 // Limiter à 4 résultats
    );

    $titres = [];
    $descriptions = [];

    // Extraire les titres et descriptions des 4 derniers événements
    foreach ($evenements as $ev) {
        $titres[] = $ev->getTitre();
        $descriptions[] = $ev->getDescription();
    }

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder les modifications
        $entityManager->persist($evenement);
        $entityManager->flush();

        // Récupérer tous les utilisateurs
        $users = $this->userRepository->findAll();

        // Créer l'e-mail à envoyer
        $email = (new Email())
            ->from('no-reply@votreplateforme.com')
            ->subject('Événement mis à jour')
            ->html('<p>Un événement a été mis à jour sur la plateforme. Consultez les nouvelles informations dès maintenant!</p>');

        // Ajouter les destinataires à l'email
        foreach ($users as $user) {
            $this->mailer->send($email->to($user->getEmail()));
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('evenement/edit.html.twig', [
        'evenement' => $evenement,
        'form' => $form,
        'titres' => $titres, // Passer les titres à la vue
        'descriptions' => $descriptions, // Passer les descriptions à la vue
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

