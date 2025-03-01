<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    public function searchByNameAndId(string $query, string $sortBy = 'nom', string $order = 'ASC'): array
    {
        // Validation des valeurs de tri pour éviter les erreurs de SQL injection
        $validSortFields = ['titre', 'id'];  // Champs autorisés pour le tri
        $validOrder = ['ASC', 'DESC']; // Ordre autorisé
        
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'titre'; // Valeur par défaut si le critère de tri est invalide
        }
        
        if (!in_array($order, $validOrder)) {
            $order = 'ASC'; // Valeur par défaut si l'ordre est invalide
        }
        
        // Recherche par nom ou id
        return $this->createQueryBuilder('c')
            ->where('c.titre LIKE :queryName OR c.id = :queryId') // Recherche par nom ou id
            ->setParameter('queryName', '%' . $query . '%')  // Recherche partielle pour le nom
            ->setParameter('queryId', $query) // Recherche exacte pour l'id
            ->orderBy('c.' . $sortBy, $order)  // Applique le tri
            ->getQuery()
            ->getResult();
    }
    
}
