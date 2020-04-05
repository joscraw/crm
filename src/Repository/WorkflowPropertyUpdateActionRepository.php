<?php

namespace App\Repository;

use App\Entity\WorkflowPropertyUpdateAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WorkflowPropertyUpdateAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowPropertyUpdateAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowPropertyUpdateAction[]    findAll()
 * @method WorkflowPropertyUpdateAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowPropertyUpdateActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowPropertyUpdateAction::class);
    }

    // /**
    //  * @return WorkflowPropertyUpdateAction[] Returns an array of WorkflowPropertyUpdateAction objects
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
    public function findOneBySomeField($value): ?WorkflowPropertyUpdateAction
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
