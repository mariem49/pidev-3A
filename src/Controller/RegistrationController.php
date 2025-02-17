<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'registration')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        // Create a new user object
        $user = new User();

        // Create the registration form
        $form = $this->createForm(UserType::class, $user);

        // Handle the request (form submission)
        $form->handleRequest($request);

        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the user's password
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Set the default role (you can change this depending on your requirements)
            $user->setRole('ROLE_USER'); 

            // Persist the user to the database
            $em->persist($user);
            $em->flush();

            // Add a success message (optional)
            $this->addFlash('success', 'Registration successful! You can now log in.');

            // Redirect to login page after successful registration
            return $this->redirectToRoute('app_login');
        }

        // Render the registration form template
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(), // Use the same variable name as in the template
        ]);
    }
}


