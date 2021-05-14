<?php

namespace App\Repository;

use App\Entity\GmailAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GmailAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method GmailAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method GmailAttachment[]    findAll()
 * @method GmailAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GmailAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmailAttachment::class);
    }

    // /**
    //  * @return GmailAttachment[] Returns an array of GmailAttachment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GmailAttachment
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
