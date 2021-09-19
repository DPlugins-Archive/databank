<?php

namespace App\Repository;

use App\Entity\BillingHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BillingHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method BillingHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method BillingHistory[]    findAll()
 * @method BillingHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingHistory::class);
    }

    // /**
    //  * @return BillingHistory[] Returns an array of BillingHistory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BillingHistory
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
