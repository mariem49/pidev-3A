<?php

namespace App\Repository;

use App\Entity\Cour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class CourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cour::class);
    }

    /**
     * Get the course with the most sessions
     *
     * @return Cour|null
     */
    public function getCourseWithMostSessions(): ?Cour
    {
        // Create the query builder
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.seances', 's')  // Assuming the relation between Cour and Seance is 'seances'
            ->select('c', 'COUNT(s.id) AS sessionCount')
            ->groupBy('c.id')
            ->orderBy('sessionCount', 'DESC')
            ->setMaxResults(1); // We only need the top course

        // Execute the query and return the result
        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ? $result[0] : null; // Return the course with the most sessions
    }

    /**
     * @param string|null $searchTerm
     * @param string $sortBy
     * @param string $order
     * @return Cour[]
     */
    public function searchAndSort($searchTerm = null, $sortBy = 'id', $order = 'ASC'): array
    {
        // Create the query builder for the 'Cour' entity
        $qb = $this->createQueryBuilder('c');

        // Apply search filter if the search term is provided
        if ($searchTerm) {
            $qb->where('c.nom LIKE :searchTerm')
               ->orWhere('c.id LIKE :searchTerm')
               ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Apply sorting based on the provided sort column and order
        // Ensure the 'sortBy' parameter is a valid column name in the 'Cour' entity
        if (in_array($sortBy, ['id', 'nom', 'duree'])) {  // Add any other valid fields here
            $qb->orderBy('c.' . $sortBy, $order);
        }

        // Execute the query and return the results
        return $qb->getQuery()->getResult();
    }
}
