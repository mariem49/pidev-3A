<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\BlogRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/posts')]
final class PostController extends AbstractController
{
    #[Route(name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/post/blogs/{id}', name: 'posts_par_blogs', methods: ['GET'])]
    public function getPostsParBlogs(
        int $id,
        PostRepository $postRepository,
        CsrfTokenManagerInterface $csrfTokenManager
    ): JsonResponse {
        $posts = $postRepository->findBy(['blog' => $id]);

        $data = array_map(function (Post $post) use ($csrfTokenManager) {
            return [
                'id' => $post->getId(),
                'content' => $post->getContent(),
                'image' => $post->getImage(),
                'createdAt' => $post->getCreatedAt()->format('d/m/Y'),
                'updatedAt' => $post->getUpdateAt()->format('d/m/Y'),
                'csrf_token' => $csrfTokenManager->getToken('delete' . $post->getId())->getValue()
            ];
        }, $posts);

        return $this->json($data);
    }


   // src/Controller/PostController.php
#[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $post = new Post();
    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $form->get('image')->getData();

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

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }
}