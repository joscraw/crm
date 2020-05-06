<?php

namespace App\Repository;

use App\Entity\AclLockable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AclLockable|null find($id, $lockMode = null, $lockVersion = null)
 * @method AclLockable|null findOneBy(array $criteria, array $orderBy = null)
 * @method AclLockable[]    findAll()
 * @method AclLockable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LockableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AclLockable::class);
    }

    // /**
    //  * @return Lockable[] Returns an array of Lockable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Lockable
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
