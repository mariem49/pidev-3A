<?php

namespace App\Controller;

use Knp\Snappy\Pdf;
use App\Entity\Cour;
use App\Form\CourType;
use App\Repository\CourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;


class CourController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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

    #[Route('/acceuilCours', name: 'afficher_cour')]
    public function indexacc(CourRepository $blogRepository, EntityManagerInterface $entityManager): Response
    {
        $cours = $entityManager->getRepository(Cour::class)->findAll();

        return $this->render('cour/accueil.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/cour/ajouter', name: 'ajouter_cour')]
public function ajouter(
    Request $request, 
    EntityManagerInterface $entityManager, 
    MailerInterface $mailer, 
    UserRepository $userRepository
): Response {
    $cour = new Cour();
    $form = $this->createForm(CourType::class, $cour);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un cours.');
        }

        $cour->setUser($user);
        $entityManager->persist($cour);
        $entityManager->flush();

        // 📢 Récupérer tous les utilisateurs
        $users = $userRepository->findAll();
        foreach ($users as $recipient) {
            $email = (new Email())
                ->from('abderrahimroua@gmail.com') 
                ->to($recipient->getEmail())
                ->subject('Nouveau Cours Disponible')
                ->html($this->renderView('cour/nouveau_cours.html.twig', [
                    'cour' => $cour,
                    'user' => $recipient,
                ]));

            $mailer->send($email);
        }

        $this->addFlash('success', 'Le cours a été ajouté avec succès et un email a été envoyé à tous les utilisateurs !');
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


    #[Route('/cours/pdf', name: 'cours_pdf')]
    public function generatePdf(Pdf $knpSnappyPdf, Environment $twig): Response
    {
        $cours = $this->entityManager->getRepository(Cour::class)->findAll();

        $html = $twig->render('cour/pdf.html.twig', [
            'cours' => $cours,
        ]);

        return new Response(
            $knpSnappyPdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="cours.pdf"',
            ]
        );
    }
}
