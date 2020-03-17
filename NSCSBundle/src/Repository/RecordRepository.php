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

    /**
     * @param $limit
     * @param $offset
     * @param $search
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChapters($limit, $offset, $search) {
        $query = "SELECT

	`zqCrP.account`.id as chapter_record_id,
	`zqCrP.account`.properties as account_properties,
	`zGyra.event`.id as event_record_id,
	`zGyra.event`.properties as event_properties,
	`DknLX.event_registration`.id as event_registration_record_id,
	`DknLX.event_registration`.properties as event_registration_properties,
	`LersM.contacts`.id as contact_record_id,
	`LersM.contacts`.properties as contact_properties
	 
	 from record `zqCrP.account`     /* Given the id \"11\" This first statement matches: {\"property_name\": \"11\"} */
     LEFT JOIN record `zGyra.event` on 
     (`zGyra.event`.properties->>'$.\"chapter\"' REGEXP concat('^', `zqCrP.account`.id, '$') AND `zGyra.event`.custom_object_id = '24')
    /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11\"} */
     OR (`zGyra.event`.properties->>'$.\"chapter\"' REGEXP concat(';', `zqCrP.account`.id, '$') AND `zGyra.event`.custom_object_id = '24')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11;13\"} */
     OR (`zGyra.event`.properties->>'$.\"chapter\"' REGEXP concat(';', `zqCrP.account`.id, ';') AND `zGyra.event`.custom_object_id = '24')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"11;12;13\"} */
     OR (`zGyra.event`.properties->>'$.\"chapter\"' REGEXP concat('^', `zqCrP.account`.id, ';') AND `zGyra.event`.custom_object_id = '24')     /* Given the id \"11\" This first statement matches: {\"property_name\": \"11\"} */
     LEFT JOIN record `DknLX.event_registration` on 
     (`DknLX.event_registration`.properties->>'$.\"event\"' REGEXP concat('^', `zGyra.event`.id, '$') AND `DknLX.event_registration`.custom_object_id = '25')
    /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"event\"' REGEXP concat(';', `zGyra.event`.id, '$') AND `DknLX.event_registration`.custom_object_id = '25')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11;13\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"event\"' REGEXP concat(';', `zGyra.event`.id, ';') AND `DknLX.event_registration`.custom_object_id = '25')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"11;12;13\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"event\"' REGEXP concat('^', `zGyra.event`.id, ';') AND `DknLX.event_registration`.custom_object_id = '25') 
    /* Given the id \"11\" This first statement matches: {\"property_name\": \"11\"} */
     LEFT JOIN record `LersM.contacts` on 
     (`DknLX.event_registration`.properties->>'$.\"contact\"' REGEXP concat('^', `LersM.contacts`.id, '$') AND `LersM.contacts`.custom_object_id = '1')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"contact\"' REGEXP concat(';', `LersM.contacts`.id, '$') AND `LersM.contacts`.custom_object_id = '1')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11;13\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"contact\"' REGEXP concat(';', `LersM.contacts`.id, ';') AND `LersM.contacts`.custom_object_id = '1')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"11;12;13\"} */
     OR (`DknLX.event_registration`.properties->>'$.\"contact\"' REGEXP concat('^', `LersM.contacts`.id, ';')AND `LersM.contacts`.custom_object_id = '1')
 WHERE 
 (
`zqCrP.account`.custom_object_id = 2
) ";

        $query .= !empty($search) ? sprintf(" AND LOWER(`zqCrP.account`.properties) LIKE \"%%%s%%\"", strtolower($search)) : '';
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
     * This query does a few things.
     * 1. Pulls all the contacts for a given chapter
     * 2. Grabs their associated chapter leadership if it exists
     * 3. Grabs their associated chapter to the chapter leadership as well
     * @param $limit
     * @param $offset
     * @param $search
     * @param $chapterRecordId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChapterContacts($chapterRecordId, $limit = null, $offset = null, $search = null) {
        $query = sprintf("SELECT DISTINCT `5A2IB.contacts`.id as contact_record_id, 
    `5A2IB.contacts`.properties as contact_properties, 
    `NiVW9.account`.id as chapter_record_id,
    `NiVW9.account`.properties as chapter_properties, 
    `PJ8cu.chapter_leadership`.id as chapter_leadership_record_id,
    `PJ8cu.chapter_leadership`.properties as chapter_leadership_properties 
    from record `5A2IB.contacts` 
    /* Given the id \"11\" This first statement matches: {\"property_name\": \"11\"} */
     LEFT JOIN record `PJ8cu.chapter_leadership` on 
     (`5A2IB.contacts`.properties->>'$.\"chapter_leadership\"' REGEXP concat('^', `PJ8cu.chapter_leadership`.id, '$') AND `PJ8cu.chapter_leadership`.custom_object_id = '5')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11\"} */
     OR (`5A2IB.contacts`.properties->>'$.\"chapter_leadership\"' REGEXP concat(';', `PJ8cu.chapter_leadership`.id, '$') AND `PJ8cu.chapter_leadership`.custom_object_id = '5')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11;13\"} */
     OR (`5A2IB.contacts`.properties->>'$.\"chapter_leadership\"' REGEXP concat(';', `PJ8cu.chapter_leadership`.id, ';') AND `PJ8cu.chapter_leadership`.custom_object_id = '5')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"11;12;13\"} */
     OR (`5A2IB.contacts`.properties->>'$.\"chapter_leadership\"' REGEXP concat('^', `PJ8cu.chapter_leadership`.id, ';')AND `PJ8cu.chapter_leadership`.custom_object_id = '5')
 
    /* Given the id \"11\" This first statement matches: {\"property_name\": \"11\"} */
     LEFT JOIN record `NiVW9.account` on 
     (`PJ8cu.chapter_leadership`.properties->>'$.\"chapter\"' REGEXP concat('^', `NiVW9.account`.id, '$') AND `NiVW9.account`.custom_object_id = '2')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11\"} */
     OR (`PJ8cu.chapter_leadership`.properties->>'$.\"chapter\"' REGEXP concat(';', `NiVW9.account`.id, '$') AND `NiVW9.account`.custom_object_id = '2')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"12;11;13\"} */
     OR (`PJ8cu.chapter_leadership`.properties->>'$.\"chapter\"' REGEXP concat(';', `NiVW9.account`.id, ';') AND `NiVW9.account`.custom_object_id = '2')
     /* Given the id \"11\" This second statement matches: {\"property_name\": \"11;12;13\"} */
     OR (`PJ8cu.chapter_leadership`.properties->>'$.\"chapter\"' REGEXP concat('^', `NiVW9.account`.id, ';')AND `NiVW9.account`.custom_object_id = '2')
    WHERE `5A2IB.contacts`.custom_object_id = 1 AND `5A2IB.contacts`.properties->\"$.account_name\" = \"%s\"", $chapterRecordId);

        if($search) {
            $query .= !empty($search) ? sprintf(" AND LOWER(`5A2IB.contacts`.properties) LIKE \"%%%s%%\"", strtolower($search)) : '';
        }

        if($limit && $offset) {
            $query .= sprintf(" LIMIT %s OFFSET %s", $limit, $offset);
        }

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
     * @param $chapterRecordId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getChapterEvents($chapterRecordId, $limit = null, $offset = null, $search = null) {
        $query = sprintf("SELECT root.id, root.properties from record root INNER JOIN custom_object co on root.custom_object_id = co.id 
WHERE co.internal_name = 'event' AND root.properties->\"$.chapter\" = \"%s\"", $chapterRecordId);

        if($search) {
            $query .= !empty($search) ? sprintf(" AND LOWER(root.properties) LIKE \"%%%s%%\"", strtolower($search)) : '';
        }

        if($limit && $offset) {
            $query .= sprintf(" LIMIT %s OFFSET %s", $limit, $offset);
        }

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return array(
            "results"  => $results
        );
    }

}
