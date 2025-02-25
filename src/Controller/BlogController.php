<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route(name: 'app_blog_index', methods: ['GET'])]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogRepository->findAll(),
        ]);
    }
    #[Route('/front/blog',name: 'app_blog_front', methods: ['GET'])]
    public function indexFront(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/front.html.twig', [
            'blogs' => $blogRepository->findAll(),
        ]);
    }


   /* #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }*/
    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $blog = new Blog();
    $form = $this->createForm(BlogType::class, $blog);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the currently logged-in user
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create a blog.');
        }

        // Set the user before persisting
            $blog->setUser($user);

        $entityManager->persist($blog);
        $entityManager->flush();

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('blog/new.html.twig', [
        'blog' => $blog,
        'form' => $form,
    ]);
}
#[Route('/new/front', name: 'app_blog_new_front', methods: ['GET', 'POST'])]
public function newfront(Request $request, EntityManagerInterface $entityManager): Response
{
    $blog = new Blog();
    $form = $this->createForm(BlogType::class, $blog);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Get the currently logged-in user
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to create a blog.');
        }

        // Set the user before persisting
            $blog->setUser($user);

        $entityManager->persist($blog);
        $entityManager->flush();

        return $this->redirectToRoute('app_blog_front', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('blog/newfront.html.twig', [
        'blog' => $blog,
        'form' => $form,
    ]);
}
#[Route('/{id}/delete/front', name: 'app_blog_delete_front', methods: ['POST'])]
public function deletez(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->request->get('_token'))) {
        // Supprimer les posts liés
        foreach ($blog->getPosts() as $post) {
            $entityManager->remove($post);
        }
        
        // Maintenant, supprimer le blog
        $entityManager->remove($blog);
        $entityManager->flush();
    }

    return $this->redirectToRoute('app_blog_front', [], Response::HTTP_SEE_OTHER);
}




    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }
    
    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit/front', name: 'app_blog_edit_front', methods: ['GET', 'POST'])]
    public function editt(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_front', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/editfront.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }
}  
