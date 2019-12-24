<?php

namespace NSCSBundle\Repository;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
use App\EntityListener\PropertyListener;
use App\Model\FieldCatalog;
use App\Model\NumberField;
use App\Utils\ArrayHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
    }

    // /**
    //  * @return Record[] Returns an array of Record objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Record
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param $email
     * @param $invitationCode
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getContactByEmailAndInvitationCode($email, $invitationCode) {

        $query = sprintf("SELECT DISTINCT root.id from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
                                WHERE co.internal_name = 'contacts' AND `root`.properties->>'$.email' = '%s'
                                AND `root`.properties->>'$.invitation_code' = '%s' LIMIT 0, 1", $email, $invitationCode);
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @param $email
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getContactByEmail($email) {

        $query = sprintf("SELECT DISTINCT root.id from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
                                WHERE co.internal_name = 'contacts' AND `root`.properties->>'$.email' = '%s'
                                 LIMIT 0, 1", $email);
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @param $invitationCode
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getContactByInvitationCode($invitationCode) {

        $query = sprintf("SELECT DISTINCT root.id from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
                                WHERE co.internal_name = 'contacts' AND `root`.properties->>'$.invitation_code' = '%s' LIMIT 0, 1", $invitationCode);
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @param $limit
     * @param $offset
     * @param $search
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCengageScholarships($limit, $offset, $search, $tag) {
        $query = "SELECT DISTINCT root.id, root.properties from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
                                WHERE co.internal_name = 'cengage_scholarships'";
        $query .= !empty($search) ? sprintf(" AND LOWER(root.properties) LIKE \"%%%s%%\"", strtolower($search)) : '';
        $query .= !empty($tag) ? sprintf(" AND LOWER(root.properties->\"$.subject\") LIKE \"%%%s%%\"", strtolower($tag)) : '';
        $query .= sprintf(" LIMIT %s OFFSET %s", $limit, $offset);
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @param $search
     * @param $tag
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCengageScholarshipCount($search, $tag) {
        $query = "SELECT count(root.id) as count from record root INNER JOIN custom_object co on root.custom_object_id = co.id
WHERE co.internal_name = 'cengage_scholarships'";
        $query .= !empty($search) ? sprintf(" AND LOWER(root.properties) LIKE \"%%%s%%\"", strtolower($search)) : '';
        $query .= !empty($tag) ? sprintf(" AND LOWER(root.properties->\"$.subject\") LIKE \"%%%s%%\"", strtolower($tag)) : '';
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCengageScholarshipTags() {
        $query = "SELECT DISTINCT root.properties->\"$.subject\" as subject from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
                                WHERE co.internal_name = 'cengage_scholarships' and root.properties->\"$.subject\" != \"\" GROUP BY subject";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

}
