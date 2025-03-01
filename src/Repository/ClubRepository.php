<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Club::class);
    }

    //    /**
    //     * @return Club[] Returns an array of Club objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Club
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    
  public function searchByNameAndId(string $query, string $sortBy = 'nom', string $order = 'ASC'): array
{
    // Validation des valeurs de tri pour éviter les erreurs de SQL injection
    $validSortFields = ['nom', 'id'];  // Champs autorisés pour le tri
    $validOrder = ['ASC', 'DESC']; // Ordre autorisé
    
    if (!in_array($sortBy, $validSortFields)) {
        $sortBy = 'nom'; // Valeur par défaut si le critère de tri est invalide
    }
    
    if (!in_array($order, $validOrder)) {
        $order = 'ASC'; // Valeur par défaut si l'ordre est invalide
    }
    
    // Recherche par nom ou id
    return $this->createQueryBuilder('c')
        ->where('c.nom LIKE :queryName OR c.id = :queryId') // Recherche par nom ou id
        ->setParameter('queryName', '%' . $query . '%')  // Recherche partielle pour le nom
        ->setParameter('queryId', $query) // Recherche exacte pour l'id
        ->orderBy('c.' . $sortBy, $order)  // Applique le tri
        ->getQuery()
        ->getResult();
}


public function countClubsByType(): array
{
    return $this->createQueryBuilder('c')
    ->select('c.type as type, COUNT(c.id) as count')
        ->groupBy('c.type')
        ->getQuery()
        ->getResult();
}



}
