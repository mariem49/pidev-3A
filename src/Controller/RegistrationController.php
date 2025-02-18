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
        // Création d'un nouvel utilisateur
        $user = new User();

        // Création du formulaire
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Hachage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Définition du rôle par défaut
            $user->setRole('ROLE_USER');

            // Gestion de l'upload de l'image de profil
            $profilePicFile = $form->get('profilepic')->getData();

            if ($profilePicFile) {
                $originalFilename = pathinfo($profilePicFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePicFile->guessExtension();

                try {
                    // Déplacement du fichier vers le répertoire de stockage
                    $profilePicFile->move(
                        $this->getParameter('profile_pictures_directory'),
                        $newFilename
                    );
                    // Mise à jour du champ image dans l'entité User
                    $user->setProfilepic($newFilename);
                } catch (FileException $e) {
                    // Gestion des erreurs lors de l'upload
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }

            // Sauvegarde de l'utilisateur en base de données
            $em->persist($user);
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Registration successful! You can now log in.');

            // Redirection vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Rendu du formulaire
        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
