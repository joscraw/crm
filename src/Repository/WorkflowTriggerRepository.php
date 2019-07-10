<?php

namespace App\Repository;

use App\Entity\WorkflowTrigger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkflowTrigger|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowTrigger|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowTrigger[]    findAll()
 * @method WorkflowTrigger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowTriggerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkflowTrigger::class);
    }

    // /**
    //  * @return WorkflowTrigger[] Returns an array of WorkflowTrigger objects
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
    public function findOneBySomeField($value): ?WorkflowTrigger
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
