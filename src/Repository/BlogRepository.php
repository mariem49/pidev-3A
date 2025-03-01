<?php

namespace App\Repository;

use App\Entity\Blog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Blog>
 */
class BlogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blog::class);
    }

    /**
     * Recherche et tri des blogs par titre ou contenu
     */
    public function searchAndSort(string $query, string $sort = 'id', string $order = 'ASC'): array
    {
        $validOrder = ['ASC', 'DESC'];
        $validSortFields = ['id', 'title', 'description', 'createdAtBlog'];

        if (!in_array($order, $validOrder)) {
            $order = 'ASC';
        }

        if (!in_array($sort, $validSortFields)) {
            $sort = 'id';
        }

        return $this->createQueryBuilder('b')
            ->where('b.title LIKE :query OR b.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('b.' . $sort, $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre de blogs par utilisateur
     */
    public function countBlogsByUser(): array
    {
        return $this->createQueryBuilder('b')
            ->select('IDENTITY(b.user) as user_id, COUNT(b.id) as blog_count')
            ->groupBy('b.user')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche et tri des blogs (version Query pour pagination)
     */
    public function searchAndSortQuery(string $query, string $sort, string $order)
    {
        $validSortFields = ['id', 'title', 'description', 'createdAtBlog'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'id';
        }
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        return $this->createQueryBuilder('b')
            ->where('b.title LIKE :query OR b.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('b.' . $sort, $order)
            ->getQuery();
    }
}
