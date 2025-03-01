<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Snappy\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\EmailVerificationService;
use App\Service\PasswordGeneratorService; 



#[Route('/user')]
final class UserController extends AbstractController
{
    /*#[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $sort = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');
        $search = $request->query->get('search', '');

        $users = $userRepository->searchAndSort($search, $sort, $order);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'sortBy' => $sort,
            'order' => $order,
            'search' => $search,
            'stats' => $userRepository->countUsersByRole()
        ]);
    }
*/

private PasswordGeneratorService $passwordGeneratorService;

 // Injection du service PasswordGeneratorService
 public function __construct(PasswordGeneratorService $passwordGeneratorService)
 {
     $this->passwordGeneratorService = $passwordGeneratorService;
 }

#[Route(name: 'app_user_index', methods: ['GET'])]
public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
{
    $sort = $request->query->get('sort', 'id');
    $order = $request->query->get('order', 'ASC');
    $search = $request->query->get('search', '');

    $query = $userRepository->searchAndSort($search, $sort, $order);

    // Ajout de la pagination
    $users = $paginator->paginate(
        $query, // La requête DQL ou QueryBuilder
        $request->query->getInt('page', 1), // Page actuelle, par défaut 1
        10 // Nombre d'éléments par page
    );

    return $this->render('user/index.html.twig', [
        'users' => $users,
        'sortBy' => $sort,
        'order' => $order,
        'search' => $search,
        'stats' => $userRepository->countUsersByRole()
    ]);
}





    #[Route('/stats', name: 'app_user_stats', methods: ['GET'])]
    public function userStats(UserRepository $userRepository): JsonResponse
    {
        $rolesDistribution = array_map(function ($stat) {
            return ['role' => $stat['role'], 'user_count' => (int) $stat['user_count']];
        }, $userRepository->countUsersByRole());

        $stats = [
            'totalUsers' => $userRepository->count([]),
            'rolesDistribution' => $rolesDistribution
        ];

        return $this->json($stats);
    }

   /*#[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le rôle est valide
            $validRoles = ['USER', 'ADMIN'];
            if (!in_array($user->getRole(), $validRoles)) {
                $this->addFlash('error', 'Rôle invalide.');
                return $this->redirectToRoute('app_user_new');
            }

            $profilePicFile = $form->get('profilepic')->getData();
            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                try {
                    $profilePicFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    $user->setProfilepic($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload de l'image de profil
        $profilePicFile = $form->get('profilepic')->getData();

        if ($profilePicFile) {
            $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

            try {
                // Déplacement du fichier vers le répertoire de stockage
                $profilePicFile->move(
                    $this->getParameter('profile_pictures_directory'),  // Fixed here
                    $newFilename
                );
                // Mise à jour du champ image dans l'entité User
                $user->setProfilepic($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'An error occurred while uploading the image.');
            }
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/new.html.twig', [
        'form' => $form->createView(),
    ]);
}





    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

   /* #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le rôle est valide avant d’enregistrer
            $validRoles = ['USER', 'ADMIN'];
            if (!in_array($user->getRole(), $validRoles)) {
                $this->addFlash('error', 'Rôle invalide.');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }*/

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle profile picture update
            $profilePicFile = $form->get('profilepic')->getData();
    
            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();
    
                try {
                    // Move the file to the directory
                    $profilePicFile->move(
                        $this->getParameter('profile_pictures_directory'),  // Fixed here
                        $newFilename
                    );
                    // Update the image in the User entity
                    $user->setProfilepic($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }
    
            $entityManager->flush();
    
            // Redirect to the user list after editing
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),  // Pass form view to the template
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Correct CSRF token check
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


   

   

    #[Route('/generate-password', name: 'generate_password')]
    public function generatePassword(): Response
    {
        // Appel au service pour générer un mot de passe
        $password = $this->passwordGeneratorService->generatePassword(12, 'all');

        // Affichage du mot de passe généré
        return $this->json(['password' => $password]);
    }




}
