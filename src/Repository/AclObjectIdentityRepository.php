<?php

namespace App\Repository;

use App\Entity\AclObjectIdentity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AclObjectIdentity|null find($id, $lockMode = null, $lockVersion = null)
 * @method AclObjectIdentity|null findOneBy(array $criteria, array $orderBy = null)
 * @method AclObjectIdentity[]    findAll()
 * @method AclObjectIdentity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AclObjectIdentityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AclObjectIdentity::class);
    }

    // /**
    //  * @return AclObjectIdentity[] Returns an array of AclObjectIdentity objects
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
    public function findOneBySomeField($value): ?AclObjectIdentity
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