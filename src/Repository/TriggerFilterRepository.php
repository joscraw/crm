<?php

namespace App\Repository;

use App\Entity\TriggerFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TriggerFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method TriggerFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method TriggerFilter[]    findAll()
 * @method TriggerFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TriggerFilterRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TriggerFilter::class);
    }

    // /**
    //  * @return TriggerFilter[] Returns an array of TriggerFilter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TriggerFilter
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
