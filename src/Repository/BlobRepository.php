<?php

namespace App\Repository;

use App\Entity\Blob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Blob|null find($id, $lockMode = null, $lockVersion = null)
 * @method Blob|null findOneBy(array $criteria, array $orderBy = null)
 * @method Blob[]    findAll()
 * @method Blob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Blob::class);
    }

    // /**
    //  * @return Blob[] Returns an array of Blob objects
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
    public function findOneBySomeField($value): ?Blob
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
