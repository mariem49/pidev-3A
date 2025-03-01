<?php

namespace App\Controller;

use App\Entity\Club;
use App\Form\ClubType;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\{FormError, FormInterface};
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/club')]
final class ClubController extends AbstractController
{
    #[Route(name: 'app_club_index', methods: ['GET'])]
    public function index(ClubRepository $clubRepository, Request $request): Response
    {
        $validSortByFields = ['nom', 'id']; // Valid sort fields
        $sortBy = $request->query->get('sort', 'nom'); // Default sort by 'nom'
        $order = $request->query->get('order', 'ASC'); // Default order 'ASC'
        
        if (!in_array($sortBy, $validSortByFields)) {
            $sortBy = 'nom'; // Fallback to 'nom' if invalid sort
        }
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC'; // Fallback to 'ASC' if invalid order
        }
        
        // Fetch clubs sorted by the chosen criteria
        $clubs = $clubRepository->findBy([], [$sortBy => $order]);
        
        // Fetch statistics for the chart
        $stats = $clubRepository->countClubsByType();
        
        // Pass the stats to the view
        return $this->render('club/index.html.twig', [
            'clubs' => $clubs,
            'sortBy' => $sortBy,
            'order' => $order,
            'stats' => $stats,
        ]);
}


#[Route('/club/index', name: 'app_club_index_front', methods: ['GET'])]
public function indexFront(Request $request, ClubRepository $clubRepository): Response
{
    // Get the sorting parameters from the request or use defaults
    $sortBy = $request->query->get('sort', 'id'); // Default sorting by 'id'
    $order = $request->query->get('order', 'ASC'); // Default sorting order is 'ASC'

    // Make sure the sorting values are valid
    $validSortFields = ['id', 'nom']; // Add more fields here if needed
    $validOrder = ['ASC', 'DESC'];

    if (!in_array($sortBy, $validSortFields)) {
        $sortBy = 'id'; // Default to 'id' if the sort value is invalid
    }

    if (!in_array($order, $validOrder)) {
        $order = 'ASC'; // Default to 'ASC' if the order value is invalid
    }

    // Fetch clubs from the database with sorting applied
    $clubs = $clubRepository->findBy([], [$sortBy => $order]);

    // Render the view with sorted clubs and pass sorting variables to the template
    return $this->render('club/indexFront.html.twig', [
        'clubs' => $clubs,
        'sortBy' => $sortBy,
        'order' => $order,
        
    ]);
}

#[Route('/new', name: 'app_club_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
{
    $club = new Club();
    $form = $this->createForm(ClubType::class, $club); // Créer le formulaire basé sur ClubType pour créer un club
    $form->handleRequest($request); // Vérifier si le formulaire est soumis

    if ($form->isSubmitted() && $form->isValid()) { // Formulaire soumis et valide
        /** @var UploadedFile $imageFile */
        $imageFile = $form->get('logo')->getData(); // Récupérer l'image soumise avec le logo

        // Vérifier si un fichier a été téléchargé
        if ($imageFile) {
            // Générer un nom unique pour le fichier
            $fileName = uniqid() . '.' . $imageFile->guessExtension();

            // Déplacer le fichier vers le répertoire souhaité
            try {
                $imageFile->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                    $fileName
                );
            } catch (FileException $e) {
                // Gérer l'erreur de téléchargement du fichier
                // Par exemple, enregistrer l'erreur dans les logs ou afficher un message à l'utilisateur
                $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                return $this->redirectToRoute('app_club_new'); // Rediriger vers la page de création du club
            }

            // Définir la propriété logo avec le nom du fichier
            $club->setLogo($fileName);
        }

        // Associer l'utilisateur actuel au club
        $user = $security->getUser(); // Récupérer l'utilisateur actuellement connecté
        $club->setUser($user); // Associer l'utilisateur au club

        // Sauvegarder l'entité
        $entityManager->persist($club); // Ajouter le club à la gestion des entités pour qu'il soit sauvegardé
        $entityManager->flush(); // Sauvegarder les changements dans la base de données

        // Rediriger vers la page d'index des clubs
        return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
    }

    // Retourner le formulaire si des erreurs sont présentes ou si le formulaire n'est pas soumis
    return $this->render('club/new.html.twig', [
        'club' => $club,
        'form' => $form->createView(), // Créer la vue du formulaire
    ]);
}

    #[Route('/{id}', name: 'app_club_show', methods: ['GET'])]
    public function show(Club $club): Response
    {
        return $this->render('club/show.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/front/{id}', name: 'app_club_show_front', methods: ['GET'])]
    public function showFront(Club $club): Response
    {
        return $this->render('club/showFront.html.twig', [
            'club' => $club,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_club_edit', methods: ['GET', 'POST'])] //
    public function edit(Request $request, Club $club, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClubType::class, $club);
        $form->handleRequest($request);

        // Vérifier si le fichier logo existe
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $club->getLogo();
        $logoExists = file_exists($logoPath) && $club->getLogo();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('logo')->getData();

            // Vérifier si un fichier a été téléchargé
            if ($imageFile) {
                // Vérifier l'extension du fichier
                if (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png'])) {
                    $form->get('logo')->addError(new FormError('Le fichier doit être une image (JPG, JPEG, PNG).'));
                    return $this->render('club/edit.html.twig', [
                        'club' => $club,
                        'form' => $form,
                        'logoExists' => $logoExists,
                    ]);
                }

                // Supprimer l'ancien logo s'il existe
                if ($logoExists) {
                    unlink($logoPath);
                }

                // Générer un nom unique pour le fichier
                $fileName = uniqid() . '.' . $imageFile->guessExtension();

                // Déplacer le fichier vers le répertoire de stockage
                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/logos',
                        $fileName
                    );
                    // Mettre à jour le logo du club avec le nom du fichier
                    $club->setLogo($fileName);
                } catch (FileException $e) {
                    // Gérer l'erreur d'upload
                    $form->get('logo')->addError(new FormError('Erreur lors du téléchargement du fichier.'));
                    return $this->render('club/edit.html.twig', [
                        'club' => $club,
                        'form' => $form,
                        'logoExists' => $logoExists,
                    ]);
                }
            }

            // Sauvegarder les données
            $entityManager->flush();
            return $this->redirectToRoute('app_club_index');
        }

        return $this->render('club/edit.html.twig', [
            'club' => $club,
            'form' => $form,
            'logoExists' => $logoExists,
        ]);
    }



    #[Route('/{id}', name: 'app_club_delete', methods: ['POST'])]
    public function delete(Request $request, Club $club, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $club->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer les événements associés et leurs affiches
            $filesystem = new Filesystem();
            foreach ($club->getEvenements() as $evenement) {

                $entityManager->remove($evenement);
            }

            // Supprimer le logo du club
            $logo = $club->getLogo();
            if ($logo) {
                $filesystem->remove($this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $logo);
            }

            $entityManager->remove($club);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_club_index', [], Response::HTTP_SEE_OTHER);
    }



    

    #[Route('/search', name: 'app_club_search', methods: ['GET'])]
    public function search(Request $request, ClubRepository $clubRepository): Response
    {
        $query = $request->query->get('q'); // Récupérer la saisie utilisateur
        $clubs = $clubRepository->searchByNameAndId($query); // Recherche par nom et id
    
        $results = array_map(function (Club $club) {
            return [
                'id' => $club->getId(),
                'nom' => $club->getNom(),
            ];
        }, $clubs);
    
        return $this->json($results);
    }
    

    

    //PDF - BUNDLE
    #[Route('/download/{id}', name: 'app_download_club_pdf', methods: ['GET'])]
    public function downloadPDF(Club $club): Response
    {
    
    {
        $pdfOption = new Options();
        $pdfOption->set('defaultFont', 'Arial');
        $pdfOption->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($pdfOption);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $dompdf->setHttpContext($context);

        // 🔥 Convertir l'image en base64
        $logoBase64 = '';
        if ($club->getLogo()) {
            $logoPath = $this->getParameter('kernel.project_dir') . '/public/uploads/logos/' . $club->getLogo();
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        }

        // Rendu du template Twig en passant l'image en base64
        $html = $this->renderView('club/downloadPDF.html.twig', [
            'club' => $club,
            'logoBase64' => $logoBase64,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer le fichier PDF
        $fichier = strval($club->getId()) . $club->getNom() . '.pdf';

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fichier . '"',
        ]);
    }
}
}