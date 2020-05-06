<?php

namespace App\Repository;

use App\Entity\AclLock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AclLock|null find($id, $lockMode = null, $lockVersion = null)
 * @method AclLock|null findOneBy(array $criteria, array $orderBy = null)
 * @method AclLock[]    findAll()
 * @method AclLock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AclLockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AclLock::class);
    }

    // /**
    //  * @return AclLock[] Returns an array of AclLock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AclLock
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
