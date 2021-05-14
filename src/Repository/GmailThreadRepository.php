<?php

namespace App\Repository;

use App\Entity\GmailThread;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GmailThread|null find($id, $lockMode = null, $lockVersion = null)
 * @method GmailThread|null findOneBy(array $criteria, array $orderBy = null)
 * @method GmailThread[]    findAll()
 * @method GmailThread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GmailThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmailThread::class);
    }

    // /**
    //  * @return GmailThread[] Returns an array of GmailThread objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GmailThread
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
