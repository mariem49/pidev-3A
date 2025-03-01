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
use App\Service\PasswordGeneratorService; 


class RegistrationController extends AbstractController
{

    private $passwordGeneratorService;
    private $entityManager;

    public function __construct(
        PasswordGeneratorService $passwordGeneratorService,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordGeneratorService = $passwordGeneratorService;
        $this->entityManager = $entityManager;
        
    }


   /* #[Route('/registration', name: 'registration')]
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
    }*/
    #[Route('/registration', name: 'registration')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger, PasswordGeneratorService $passwordGeneratorService)
    {
        // 1. Generate a secure password using the service
        $generatedPassword = $passwordGeneratorService->generatePassword();  // The generated password from the API or your logic
    
        // 2. Create a new user
        $user = new User();
        $user->setPassword($generatedPassword); // Set the generated password (this will be hashed later)
    
        // 3. Create the registration form with the generated password
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        // 4. Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // 5. Hash the password (using the generated password)
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
    
            // Set user role (default 'ROLE_USER')
            $user->setRole('ROLE_USER');
    
            // Handle the profile picture upload (if provided)
            $profilePicFile = $form->get('profilepic')->getData();
            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();
    
                try {
                    // Move the file to the desired directory
                    $profilePicFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
    
                    // Update the user's profile picture field
                    $user->setProfilepic($newFilename);
                } catch (FileException $e) {
                    // Handle file upload errors
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }
    
            // Save the user to the database
            $em->persist($user);
            $em->flush();
    
            // Success message
            $this->addFlash('success', 'Registration successful! You can now log in.');
    
            // Redirect to login page
            return $this->redirectToRoute('app_login');
        }
    
        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
            'generatedPassword' => $generatedPassword, // Pass the generated password (optional)
        ]);
    }
    
    





}
