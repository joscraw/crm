<?php

namespace App\Repository;

use App\Entity\CustomObject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CustomObject|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomObject|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomObject[]    findAll()
 * @method CustomObject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomObjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomObject::class);
    }

    // /**
    //  * @return CustomObject[] Returns an array of CustomObject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CustomObject
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
