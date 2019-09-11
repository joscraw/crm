<?php

namespace App\Repository;

use App\Entity\SendEmailAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SendEmailAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method SendEmailAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method SendEmailAction[]    findAll()
 * @method SendEmailAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SendEmailActionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SendEmailAction::class);
    }

    // /**
    //  * @return SendEmailAction[] Returns an array of SendEmailAction objects
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
    public function findOneBySomeField($value): ?SendEmailAction
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
