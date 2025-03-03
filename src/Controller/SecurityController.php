<?php

namespace App\Controller;

use App\Form\ForgotPasswordType; // Make sure this exists
use App\Repository\UserRepository; // Ensure this is the correct namespace
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Form\ForgetPasswordType;
use App\Repository\BlogRepository;
use Symfony\Bundle\MonologBundle\DependencyInjection\Compiler\AddSwiftMailerTransportPass;

final class SecurityController extends AbstractController
{
    #[Route('/security', name: 'app_security')]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
            'blogs' => $blogRepository->findAll(),

        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect_by_role');
        }
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout() 
    {
        return $this->redirectToRoute("app_login");
    }

   







#[Route('/resetpassword/{token}', name: 'app_reset_password')]
public function resetpassword(
    Request $request, 
    string $token, 
    UserPasswordHasherInterface $passwordHasher, 
    EntityManagerInterface $entityManager
) {
    $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

    if (!$user) {
        $this->addFlash('danger', 'TOKEN INCONNU');
        return $this->redirectToRoute('app_login');
    }

    if ($request->isMethod('POST')) {
        $user->setResetToken(null);

        // Hash and update the new password
        $hashedPassword = $passwordHasher->hashPassword($user, $request->request->get('password'));
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('message', 'Mot de passe mis à jour !');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/resetPassword.html.twig', ['token' => $token]);
}
#[Route(path: '/redirect-by-role', name: 'app_redirect_by_role')]
public function redirectByRole(): RedirectResponse
{
    if ($this->getUser()) {
        $roles = $this->getUser()->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return $this->redirectToRoute('app_user_index');
        } elseif (in_array('ROLE_USER', $roles)) {
            return $this->redirectToRoute('afficher_acceuil');
        } 
    }
    return $this->redirectToRoute('app_login');


    // Si aucun rôle trouvé, on redirige vers la page d'accueil
}



    #[Route(path: '/forget', name: 'forget')]
    public function forgetPassword(
        Request $request, 
        UserRepository $userRepository, 
        MailerInterface $mailer, 
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(ForgetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getData();
            $user = $userRepository->findOneBy(['email' => $donnees]);

            if (!$user) {
                $this->addFlash('danger', 'Cette adresse n\'existe pas.');
                //return $this->redirectToRoute('app_login');
            }

            // Generate reset token
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->addFlash('warning', 'Une erreur est survenue: ' . $exception->getMessage());
               return $this->redirectToRoute("app_login");
            }

            // Generate reset link
            $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // Symfony Mailer - Email creation
            $email = (new Email())
                ->from('abderrahimroua@gmail.com')
                ->to($user->getEmail())
                ->subject('Mot de passe oublié')
                ->html("<p>Bonjour,</p><p>Une demande de réinitialisation de mot de passe a été effectuée.</p><p>Veuillez cliquer sur le lien suivant : <a href='$url'>$url</a></p>");

            // Send email
            $mailer->send($email);

            $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé.');
        }

        return $this->render("security/forgetPassword.html.twig", [
            'form' => $form->createView(),
        ]);
    }
}



