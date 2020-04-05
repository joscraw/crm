<?php

namespace App\Repository;

use App\Entity\WorkflowAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WorkflowAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowAction[]    findAll()
 * @method WorkflowAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowAction::class);
    }

    // /**
    //  * @return WorkflowAction[] Returns an array of WorkflowAction objects
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
    public function findOneBySomeField($value): ?WorkflowAction
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
