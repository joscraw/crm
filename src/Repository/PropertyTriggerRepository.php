<?php

namespace App\Repository;

use App\Entity\PropertyTrigger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PropertyTrigger|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertyTrigger|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertyTrigger[]    findAll()
 * @method PropertyTrigger[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyTriggerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyTrigger::class);
    }

    // /**
    //  * @return PropertyTrigger[] Returns an array of PropertyTrigger objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PropertyTrigger
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
