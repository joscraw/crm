<?php

namespace App\Repository;

use App\Entity\ObjectWorkflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ObjectWorkflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ObjectWorkflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ObjectWorkflow[]    findAll()
 * @method ObjectWorkflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ObjectWorkflowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ObjectWorkflow::class);
    }

    // /**
    //  * @return ObjectWorkflow[] Returns an array of ObjectWorkflow objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ObjectWorkflow
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getWorkflowById($id)
    {
        return $this->createQueryBuilder('objectWorkflow')
            ->innerJoin('objectWorkflow.triggers', 'triggers')
            ->where('workflow.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
