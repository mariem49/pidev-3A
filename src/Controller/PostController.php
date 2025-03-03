<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\HistoriqueLogger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



#[Route('/posts')]
final class PostController extends AbstractController
{
    public function __construct(private HistoriqueLogger $historiqueLogger) {}

    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $sort = $request->query->get('sort', 'id');
        $order = strtoupper($request->query->get('order', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $page = $request->query->getInt('page', 1);

        // Sécurisation des paramètres de tri
        $validSorts = ['id', 'content', 'createdAt', 'updateAt'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'id';
        }

        $query = $postRepository->createQueryBuilder('p')
            ->orderBy("p.$sort", $order)
            ->getQuery();

        $pagination = $paginator->paginate($query, $page, 5);
        $statsByUser = $postRepository->countPostsByUser();
        $statsByMonth = $postRepository->countPostsByMonth();

        return $this->render('post/index.html.twig', [
            'pagination' => $pagination,
            'sortBy' => $sort,
            'order' => $order,
            'statsByUser' => $statsByUser ?: [],
            'statsByMonth' => $statsByMonth ?: [],
        ]);
    }

    /*#[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $post->setUser($user);
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->historiqueLogger->log("New post created with ID {$post->getId()} and content: {$post->getContent()}");

            $this->addFlash('success', 'Post créé avec succès !');

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }*/
     // src/Controller/PostController.php
#[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $post = new Post();
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
       
        $file = $form->get('image')->getData();
        $user = $this->getUser();

        if ($file) {
            // Générer un nom unique pour le fichier
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($filename); // Convertir en slug
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            // Déplacer le fichier dans le répertoire de destination
            try {
                $file->move(
                    $this->getParameter('images_directory'), // Le répertoire où les images seront stockées
                    $newFilename
                );
                // Sauvegarder le nom du fichier dans l'entité
                $post->setImage($newFilename);
            } catch (FileException $e) {
                // Gérer l'exception si l'enregistrement échoue
                $this->addFlash('error', 'Le téléchargement de l\'image a échoué.');
                return $this->redirectToRoute('app_post_new');
            }
        }

        // Sauvegarder l'entité dans la base de données
        $post->setUser($user);
        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('post/new.html.twig', [
        'post' => $post,
        'form' => $form->createView(),
    ]);
}



    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        $this->historiqueLogger->log("Viewed Post ID {$post->getId()} with content: {$post->getContent()}");

        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->historiqueLogger->log("Edited Post ID {$post->getId()} with new content: {$post->getContent()}");

            $this->addFlash('success', 'Post mis à jour avec succès !');

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();

            $this->historiqueLogger->log("Deleted post with ID {$post->getId()}");
            $this->addFlash('danger', 'Post supprimé avec succès !');
        }

        return $this->redirectToRoute('app_post_index');
    }
}
