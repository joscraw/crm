<?php

namespace App\Repository;

use App\Entity\Spreadsheet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Spreadsheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spreadsheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spreadsheet[]    findAll()
 * @method Spreadsheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpreadsheetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Spreadsheet::class);
    }

    // /**
    //  * @return Spreadsheet[] Returns an array of Spreadsheet objects
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
    public function findOneBySomeField($value): ?Spreadsheet
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
