<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Form\SeanceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Cour;

final class FrontController extends AbstractController
{
    #[Route('/acceuil', name: 'afficher_acceuil')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $cours = $entityManager->getRepository(Cour::class)->findAll();

        return $this->render('front/index.html.twig', [
            'cours' => $cours,
        ]);
    }
}
