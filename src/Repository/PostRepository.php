<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Func;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Recherche et trie les posts par contenu, ID ou date de création
     */
    public function searchAndSortPosts(?string $query = null, ?string $sort = 'id', ?string $order = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('p');

        // Recherche par contenu
        if ($query) {
            $qb->andWhere('p.content LIKE :query')
               ->setParameter('query', "%$query%");
        }

        // Vérifier et appliquer le tri
        $validSorts = ['id', 'content', 'createdAt', 'updateAt']; // ✅ Correction ici
        if (in_array($sort, $validSorts)) {
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            $qb->orderBy("p.$sort", $order);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne le nombre de posts par utilisateur
     */
    public function countPostsByUser(): array
    {
        return $this->createQueryBuilder('p')
            ->select('IDENTITY(p.user) as user_id, COUNT(p.id) as post_count')
            ->groupBy('p.user')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre de posts créés par mois
     */
    public function countPostsByMonth(): array
    {
        try {
            return $this->createQueryBuilder('p')
                ->select("DATE_FORMAT(p.createdAt, '%Y-%m') as month, COUNT(p.id) as post_count")
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }
}
