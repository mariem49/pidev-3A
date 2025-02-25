<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
//////////////admin/////////////////
#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /*#[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to the user list after saving
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),  // Pass form view to the template
        ]);
    }*/
   /* #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
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
                    $this->getParameter('"profile_pictures_directory'),
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


    /*#[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }*/
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
public function show(string $id, UserRepository $userRepository): Response
{
    $user = $userRepository->find((int) $id); // Convert to int

    if (!$user) {
        //throw $this->createNotFoundException('User not found.');
        return $this->render('baseF.html.twig', [
            'user' => $user,
        ]);
    }

    return $this->render('user/show.html.twig', [
        'user' => $user,
    ]);
}






    /*#[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect to the user list after editing
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),  // Pass form view to the template
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


///////////////////////end admin//////////////////




  //***********************************Client Start***********************************************************//
    /**
     * @Route("/client", name="display_client")
     */
    /*public function indexClient(): Response
    {

        return $this->render('Client/index.html.twig');
    }*/



    //***********************************Client End***********************************************************//

} 