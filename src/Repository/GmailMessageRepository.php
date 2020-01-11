<?php

namespace App\Repository;

use App\Entity\GmailMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GmailMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method GmailMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method GmailMessage[]    findAll()
 * @method GmailMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GmailMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmailMessage::class);
    }

    // /**
    //  * @return GmailMessage[] Returns an array of GmailMessage objects
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
    public function findOneBySomeField($value): ?GmailMessage
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
