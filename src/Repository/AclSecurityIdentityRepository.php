<?php

namespace App\Repository;

use App\Entity\AclSecurityIdentity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AclSecurityIdentity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AclSecurityIdentity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AclSecurityIdentity[]    findAll()
 * @method AclSecurityIdentity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AclSecurityIdentityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AclSecurityIdentity::class);
    }

    // /**
    //  * @return AclSecurityIdentity[] Returns an array of AclSecurityIdentity objects
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
    public function findOneBySomeField($value): ?AclSecurityIdentity
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
