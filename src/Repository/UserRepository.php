<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Recherche et tri des utilisateurs par nom ou email
     */
    public function searchAndSort(string $query, string $sort = 'id', string $order = 'ASC'): array
    {
        $validOrder = ['ASC', 'DESC'];
        $validSortFields = ['id', 'name', 'email'];

        if (!in_array($order, $validOrder)) {
            $order = 'ASC'; // Valeur par défaut si invalide
        }

        if (!in_array($sort, $validSortFields)) {
            $sort = 'id'; // Valeur par défaut si invalide
        }

        return $this->createQueryBuilder('u')
            ->where('u.name LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.' . $sort, $order)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre d'utilisateurs par rôle
     */
    public function countUsersByRole(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.role as role, COUNT(u.id) as user_count')
            ->groupBy('u.role')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche et tri des utilisateurs par nom ou email (version Query pour pagination)
     */
    public function searchAndSortQuery(string $query, string $sort, string $order)
    {
        $validSortFields = ['id', 'name', 'email']; // Champs valides pour le tri
        if (!in_array($sort, $validSortFields)) {
            $sort = 'id';
        }
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC';
        }

        return $this->createQueryBuilder('u')
            ->where('u.name LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.' . $sort, $order)
            ->getQuery();
    }
}
