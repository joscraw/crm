<?php

namespace App\Repository;

use App\Entity\WorkflowInput;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WorkflowInput|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowInput|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowInput[]    findAll()
 * @method WorkflowInput[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowInputRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowInput::class);
    }

    // /**
    //  * @return WorkflowInput[] Returns an array of WorkflowInput objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkflowInput
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
