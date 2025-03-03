<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\BlogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\HistoriqueLogger;
use App\Service\ProfanityFilter;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    private HistoriqueLogger $historiqueLogger;

    public function __construct(HistoriqueLogger $historiqueLogger)
    {
        $this->historiqueLogger = $historiqueLogger;
    }

    #[Route(name: 'app_blog_index', methods: ['GET'])]
    public function index(BlogRepository $blogRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Get sorting parameters with default values
        $sort = $request->query->get('sort', 'id');
        $order = strtoupper($request->query->get('order', 'ASC')); // Ensure uppercase for SQL compliance
        $page = $request->query->getInt('page', 1);

        // Prevent invalid sorting fields
        $allowedSortFields = ['id', 'title', 'createdAtBlog']; // Add more fields if needed
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Ensure order is either ASC or DESC
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        // Fetch sorted blogs
        $query = $blogRepository->createQueryBuilder('b')
            ->orderBy("b.$sort", $order)
            ->getQuery();

        // Paginate results
        $pagination = $paginator->paginate($query, $page, 5);

        // Get user distribution data for statistics
        $userStats = $blogRepository->countBlogsByUser();

        return $this->render('blog/index.html.twig', [
            'pagination' => $pagination,
            'sortBy' => $sort,
            'order' => $order,
            'userStats' => $userStats, // Pass statistics data to Twig
        ]);
    }

    #[Route('/front/blog', name: 'app_blog_front', methods: ['GET'])]
    public function indexFront(BlogRepository $blogRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $query = $blogRepository->createQueryBuilder('b')->getQuery();
        $blogs = $paginator->paginate($query, $page, 5);

        return $this->render('blog/front.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProfanityFilter $profanityFilter): Response
    {
        $blog = new Blog();
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $text = $blog->getDescription(); // Suppose que tu veux vérifier la description
            $result = $profanityFilter->checkProfanity($text);

            if ($result['has_profanity']) {
                $this->addFlash('error', 'La description contient des mots inappropriés.');
                return $this->redirectToRoute('app_blog_new');
            }

            $user = $this->getUser();

            if (!$user) {
                throw $this->createAccessDeniedException('You must be logged in to create a blog.');
            }

            $blog->setUser($user);
            $entityManager->persist($blog);
            $entityManager->flush();

            $this->historiqueLogger->log("New blog created with ID {$blog->getId()} and title: {$blog->getTitle()}");

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        $this->historiqueLogger->log("Viewed Blog ID {$blog->getId()} with title: {$blog->getTitle()} and description: {$blog->getDescription()}");

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

            $this->historiqueLogger->log("Edited Blog ID {$blog->getId()} with new title: {$blog->getTitle()} and description: {$blog->getDescription()}");

            return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $blog->getId(), $request->get('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();

            $this->historiqueLogger->log("Deleted blog with ID {$blog->getId()}");
        }

        return $this->redirectToRoute('app_blog_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete/front', name: 'app_blog_delete_front', methods: ['POST'])]
    public function deleteFront(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
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

    #[Route('/{id}/edit/front', name: 'app_blog_edit_front', methods: ['GET', 'POST'])]
    public function editFront(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
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

    #[Route('/new/front', name: 'app_blog_new_front', methods: ['GET', 'POST'])]
    public function newFront(Request $request, EntityManagerInterface $entityManager): Response
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
}