<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\Record;
use App\Model\FieldCatalog;
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
     * @param $selectizeAllowedSearchableProperties
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeData($search, CustomObject $allowedCustomObjectToSearch, $selectizeAllowedSearchableProperties)
    {

        $jsonExtract = "properties->>'$.%s' as %s";
        $resultStr = [];
        foreach($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",",$resultStr);
        $query = sprintf("SELECT id, properties %s from record WHERE custom_object_id='%s' AND LOWER(properties) LIKE '%%%s%%'", $resultStr, $allowedCustomObjectToSearch->getId(), strtolower($search));

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param $start
     * @param $length
     * @param $search
     * @param $orders
     * @param $columns
     * @param $propertiesForDatatable
     * @param CustomObject $customObject
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDataTableData($start, $length, $search, $orders, $columns, $propertiesForDatatable, CustomObject $customObject)
    {
        $jsonExtract = "properties->>'$.%s' as %s";
        $resultStr = [];
        foreach($propertiesForDatatable as $property) {

            // custom objects are nested json structures. Let's just grab the id out of it
            if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                $jsonExtract = "properties->>'$.%s.id' as %s";
            }

            // custom objects that allow multiple are arrays of nested json structures. Let's just grab each id out of they array
            if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT && $property->getField()->isMultiple()) {
                $jsonExtract = "properties->>'$.%s[*].id' as %s";
            }

            $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName());
        }
        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT id, %s from record WHERE custom_object_id='%s'", $resultStr, $customObject->getId());

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $query .= 'and LOWER(properties) LIKE \'%'.strtolower($searchItem).'%\'';
        }

        // Order
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {

                if(isset($order['name'])) {
                    $query .= ' ORDER BY ' . $order['name'];
                }

                $query .= ' ' . $order['dir'];
            }

        // limit
        $query .= sprintf(' LIMIT %s, %s', $start, $length);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results,
            "countResult"	=> count($results)
        );

 /*       return $results;



        // Main Query
        $mainQuerySelectColumns = ['dt.label', 'dt.createdAt'];
        $searchQuery = null;
        $query = $this->createQueryBuilder('dt')
            ->select($mainQuerySelectColumns);

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $searchQuery = 'dt.label LIKE \'%'.$searchItem.'%\'';
        }

        if ($searchQuery) {
            $query->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch($order['name']) {
                    case 'label':
                        $orderColumn = 'dt.label';
                        break;
                    case 'createdAt':
                        $orderColumn = 'dt.createdAt';
                        break;
                }
                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }*/

        $results = $query->getQuery()->getResult();
        $arrayResults = $query->getQuery()->getArrayResult();

        // Count Query
        $countQuery = $this->createQueryBuilder('dt');
        $countQuery->select('COUNT(dt)');
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" 		=> $results,
            "arrayResults"  => $arrayResults,
            "countResult"	=> $countResult
        );
    }

    /**
     * @param CustomObject $customObject
     * @return mixed
     */
    public function findCountByCustomObject(CustomObject $customObject)
    {
        return $this->createQueryBuilder('record')
            ->select('COUNT(record) as count')
            ->where('record.customObject = :customObject')
            ->setParameter('customObject', $customObject->getId())
            ->getQuery()
            ->getResult();
    }
}
