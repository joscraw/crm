<?php

namespace App\Repository;

use App\Entity\RecordDuplicate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RecordDuplicate|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecordDuplicate|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecordDuplicate[]    findAll()
 * @method RecordDuplicate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordDuplicateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecordDuplicate::class);
    }

    // /**
    //  * @return RecordDuplicate[] Returns an array of RecordDuplicate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RecordDuplicate
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
