<?php

namespace App\Repository;

use App\Entity\WorkflowSendEmailAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WorkflowSendEmailAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowSendEmailAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowSendEmailAction[]    findAll()
 * @method WorkflowSendEmailAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowSendEmailActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowSendEmailAction::class);
    }

    // /**
    //  * @return WorkflowSendEmailAction[] Returns an array of WorkflowSendEmailAction objects
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
    public function findOneBySomeField($value): ?WorkflowSendEmailAction
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
