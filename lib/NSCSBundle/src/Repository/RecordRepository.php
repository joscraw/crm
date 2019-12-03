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
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository
{

    public function __construct(RegistryInterface $registry)
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


}
