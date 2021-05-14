<?php

namespace App\Repository;

use App\Entity\GmailAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GmailAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method GmailAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method GmailAccount[]    findAll()
 * @method GmailAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GmailAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmailAccount::class);
    }

    // /**
    //  * @return Gmail[] Returns an array of Gmail objects
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
    public function findOneBySomeField($value): ?Gmail
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
