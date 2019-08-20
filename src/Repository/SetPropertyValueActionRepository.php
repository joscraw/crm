<?php

namespace App\Repository;

use App\Entity\SetPropertyValueAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SetPropertyValueAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method SetPropertyValueAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method SetPropertyValueAction[]    findAll()
 * @method SetPropertyValueAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SetPropertyValueActionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SetPropertyValueAction::class);
    }

    // /**
    //  * @return SetPropertyValueAction[] Returns an array of SetPropertyValueAction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SetPropertyValueAction
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
