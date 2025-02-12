<?php

namespace App\Repository;

use App\Entity\Advice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advice>
 */
class AdviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advice::class);
    }


    /**
     * Recherche les conseils contenant un mois spécifique.
     *
     * @param int $month Le mois à rechercher (1 à 12).
     * @return Advice[] Retourne un tableau d'entités Advice.
     */
    public function findByMonth(int $month): array
    {
        return $this->createQueryBuilder('a')
        ->andWhere('JSON_CONTAINS(a.months, :month) = 1')
        ->setParameter('month', json_encode($month)) // Encode le mois en JSON
        ->getQuery()
        ->getResult();
    }

    //    /**
//     * @return Advice[] Returns an array of Advice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    //    public function findOneBySomeField($value): ?Advice
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
