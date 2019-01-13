<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\Record;
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
     * @param $search
     * @param CustomObject $allowedCustomObjectToSearch
     * @return mixed
     */
    public function getSelectizeData($search, CustomObject $allowedCustomObjectToSearch)
    {
        // Main Query
        $mainQuerySelectColumns = ['record.properties as searchField, record.id as labelField, record.id as valueField'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('record')
            ->select($mainQuerySelectColumns)
            ->innerJoin('record.customObject', 'customObject')
            ->where('customObject = :customObject')
            ->setParameter('customObject', $allowedCustomObjectToSearch->getId());

        // Search
        if(!empty($search)) {
            $searchQuery = 'record.properties LIKE \'%'.$search.'%\'';
        }

        if ($searchQuery) {
            $query->andWhere($searchQuery);
        }

        $results = $query->getQuery()->getResult();
        return $results;
    }
}
