<?php

namespace App\Repository;

use App\Entity\GmailMessage;
use App\Entity\Portal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method GmailMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method GmailMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method GmailMessage[]    findAll()
 * @method GmailMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GmailMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GmailMessage::class);
    }

    // /**
    //  * @return GmailMessage[] Returns an array of GmailMessage objects
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
    public function findOneBySomeField($value): ?GmailMessage
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    /**
     * @param Portal $portal
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getNewestForThreads(Portal $portal) {
        $query = sprintf("SELECT gm.thread_id, gm.message_id, gm.created_at, gm.sent_to, 
        gm.sent_from, gm.subject, gm.message_body, gm.internal_date, gm.is_read
        FROM gmail_message gm
        INNER JOIN gmail_thread gt on gm.gmail_thread_id = gt.id
        INNER JOIN gmail_account ga on ga.id = gt.gmail_account_id
        WHERE gm.internal_date IN (SELECT MAX(internal_date) FROM gmail_message GROUP BY thread_id) 
        AND ga.portal_id = '%s'", $portal->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if(empty($results)) {
            return [];
        }
        return $results;
    }

    /**
     * @param Portal $portal
     * @param $threadId
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMessagesForThread(Portal $portal, $threadId) {
        $query = sprintf("SELECT gm.thread_id, gm.message_id, gm.created_at, gm.sent_to, 
        gm.sent_from, gm.subject, gm.message_body, gm.internal_date, gm.is_read
        FROM gmail_message gm
        INNER JOIN gmail_thread gt on gm.gmail_thread_id = gt.id
        INNER JOIN gmail_account ga on ga.id = gt.gmail_account_id
        WHERE gm.thread_id = '%s'
        AND ga.portal_id = '%s'", $threadId, $portal->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if(empty($results)) {
            return [];
        }
        return $results;
    }

    /**
     * @param Portal $portal
     * @param array $messageIds
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMessageIdsForPortal(Portal $portal, $messageIds = []) {
        $query = sprintf("SELECT  gm.message_id
        FROM gmail_message gm
        INNER JOIN gmail_thread gt on gm.gmail_thread_id = gt.id
        INNER JOIN gmail_account ga on ga.id = gt.gmail_account_id
        AND ga.portal_id = '%s'", $portal->getId());

        if(!empty($messageIds)) {
            $query .= ' AND gm.message_id IN ("' . implode('", "', $messageIds) . '")';
        }


        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        if(empty($results)) {
            return [];
        }
        return $results;
    }

    /*
     * SELECT gm.thread_id, gm.message_id, gm.created_at, gm.sent_to,
        gm.sent_from, gm.subject, gm.message_body, gm.internal_date, gm.is_read
        FROM gmail_message gm
        INNER JOIN gmail_thread gt on gm.gmail_thread_id = gt.id
        INNER JOIN gmail_account ga on ga.id = gt.gmail_account_id
        WHERE gm.thread_id = '16f92cf8ed9db35d'
        AND ga.portal_id = '1'
     */
}
