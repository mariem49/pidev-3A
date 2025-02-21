<?php

namespace App\Controller;
use App\Entity\Blog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BlogRepository;

final class FrontController extends AbstractController
{
    #[Route('/acceuil', name: 'afficher_acceuil')]
    public function index(BlogRepository $blogRepository): Response
    {

        return $this->render('user/accueil.html.twig', [
            'blogs' => $blogRepository->findAll(),
        ]);
    }
}
