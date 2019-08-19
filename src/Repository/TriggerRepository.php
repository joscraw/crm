<?php

namespace App\Repository;

use App\Entity\Trigger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Trigger|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trigger|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trigger[]    findAll()
 * @method Trigger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TriggerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Trigger::class);
    }

    // /**
    //  * @return Trigger[] Returns an array of Trigger objects
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
    public function findOneBySomeField($value): ?Trigger
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
