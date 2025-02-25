<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registration')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger)
    {
        
        $user = new User();

        // Création du formulaire
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        
        // verifie si l form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Haching pwd
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Défine role , par defaut chykoun user 
            $user->setRole('ROLE_USER');

           // Handling profilepic upload
            $profilePicFile = $form->get('profilepic')->getData();

            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                try {
                    // move l fichier vers le répertoire de stockage
                    $profilePicFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    
                    // Updating the image field in the User entity
                    $user->setProfilepic($newFilename);
                } catch (FileException $e) {
                    // Handling errors during upload
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }

            // Savi l user to the database
            $em->persist($user);
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Registration successful! You can now log in.');

            // Redirection vers login
            return $this->redirectToRoute('app_login');
        }

        // Rendu du form
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
