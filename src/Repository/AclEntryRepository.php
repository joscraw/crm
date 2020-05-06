<?php

namespace App\Repository;

use App\Entity\AclEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AclEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method AclEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method AclEntry[]    findAll()
 * @method AclEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AclEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AclEntry::class);
    }

    // /**
    //  * @return AclEntry[] Returns an array of AclEntry objects
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
    public function findOneBySomeField($value): ?AclEntry
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
