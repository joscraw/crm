<?php

namespace App\Repository;

use App\Entity\WorkflowLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method WorkflowLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowLog[]    findAll()
 * @method WorkflowLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowLog::class);
    }

    // /**
    //  * @return WorkflowLog[] Returns an array of WorkflowLog objects
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
    public function findOneBySomeField($value): ?WorkflowLog
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
