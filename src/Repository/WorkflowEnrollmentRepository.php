<?php

namespace App\Repository;

use App\Entity\WorkflowEnrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkflowEnrollment|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowEnrollment|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowEnrollment[]    findAll()
 * @method WorkflowEnrollment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowEnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkflowEnrollment::class);
    }

    // /**
    //  * @return WorkflowEnrollment[] Returns an array of WorkflowEnrollment objects
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
    public function findOneBySomeField($value): ?WorkflowEnrollment
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
