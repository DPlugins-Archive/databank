<?php

namespace App\Repository;

use App\Entity\Billing;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Billing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Billing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Billing[]    findAll()
 * @method Billing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BillingRepository extends ServiceEntityRepository
{
    private const DAYS_GRACE_PERIOD = 3;
    private const DAYS_EXPIRE_REMINDER = 7;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Billing::class);
    }

    // /**
    //  * @return Billing[] Returns an array of Billing objects
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
    public function findOneBySomeField($value): ?Billing
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countDowngradeExpired(): int
    {
        return $this->getExpiredQueryBuilder()->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
    }

    public function downgradeExpired(): int
    {
        $qb = $this->getExpiredQueryBuilder();

        $parameters = $qb->getParameters()->toArray();
        
        return $qb->update()
            ->set('b.isActive', ':is_active')
            ->set('b.isAutoRenewal', ':is_auto_renewal')
            ->set('b.expiredAt', ':expired_at')
            ->setParameters(array_merge(
                array_combine(
                    array_map(fn ($element) => $element->getName(), $parameters),
                    array_map(fn ($element) => $element->getValue(), $parameters)
                ),
                [
                    'is_active' => false,
                    'is_auto_renewal' => false,
                    'expired_at' => null,
                ]
            ))
            ->getQuery()
            ->execute()
        ;
    }

    private function getExpiredQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :where_is_active')
            ->andWhere('b.expiredAt IS NOT NULL')
            ->andWhere('b.expiredAt < :graced_date')
            ->setParameters([
                'where_is_active' => true,
                'graced_date' => CarbonImmutable::now()->subDays(self::DAYS_GRACE_PERIOD)
            ]);
    }

    private function getGracedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :where_is_active')
            ->andWhere('b.expiredAt IS NOT NULL')
            ->andWhere('b.expiredAt < :due_date')
            ->andWhere('b.expiredAt > :graced_date')
            ->setParameters([
                'where_is_active' => true,
                'due_date' => CarbonImmutable::now(),
                'graced_date' => CarbonImmutable::now()->subDays(self::DAYS_GRACE_PERIOD)
            ]);
    }

    private function getReminderQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isActive = :where_is_active')
            ->andWhere('b.expiredAt IS NOT NULL')
            ->andWhere('b.expiredAt < :reminder_date')
            ->setParameters([
                'where_is_active' => true,
                'reminder_date' => CarbonImmutable::now()->addDays(self::DAYS_EXPIRE_REMINDER),
            ]);
    }
}
