<?php

namespace App\Repository;

ini_set('xdebug.max_nesting_level', 100000);

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

    use ArrayHelper;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    public function __construct(RegistryInterface $registry, PropertyRepository $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
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
     * @param $recordId
     * @param $selectizeAllowedSearchableProperties
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeAllowedSearchablePropertiesById($recordId, $selectizeAllowedSearchableProperties) {
        $jsonExtract = "properties->>'$.%s' as %s";
        $resultStr = [];
        foreach($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",",$resultStr);
        $query = sprintf("SELECT id, properties %s from record WHERE id='%s'", $resultStr, $recordId);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param $recordIds
     * @param $selectizeAllowedSearchableProperties
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeAllowedSearchablePropertiesByArrayOfIds($recordIds, $selectizeAllowedSearchableProperties) {
        $jsonExtract = "properties->>'$.%s' as %s";
        $resultStr = [];
        foreach($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",",$resultStr);

        $recordIds = "'" . implode("','", $recordIds) . "'";

        $query = sprintf("SELECT id, properties %s from record WHERE id IN (%s)", $resultStr, $recordIds);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param $data
     * @param CustomObject $customObject
     * @param $columnOrder
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportData($data, CustomObject $customObject, $columnOrder)
    {

        $this->data = $data;

        // Setup fields for select
        $resultStr = $this->fields($columnOrder);
        $resultStr = implode(",",$resultStr);

        // Setup Joins
        $joins = [];
        $joins = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    /**
     * @param $data
     * @param CustomObject $customObject
     * @param $columnOrder
     * @return string
     */
    public function getReportMysqlOnly($data, CustomObject $customObject, $columnOrder)
    {

        $this->data = $data;

        // Setup fields for select
        $resultStr = $this->fields($columnOrder);
        $resultStr = implode(",",$resultStr);

        // Setup Joins
        $joins = [];
        $joins = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        return $query;
    }

    /**
     * @param $data
     * @param CustomObject $customObject
     * @param $columnOrder
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function newReportLogicBuilder($data, CustomObject $customObject, $columnOrder)
    {
        $this->data = $data;
        $root = sprintf("%s.%s", $customObject->getUid(), $customObject->getInternalName());

        // Setup fields for select
        $resultStr = $this->newFieldLogicBuilder($data);
        $resultStr = implode(",",$resultStr);
        $resultStr  = !empty($resultStr) ? ', ' . $resultStr : '';

        // Setup Joins
        $joins = [];
        $joins = $this->newJoinLogicBuilder($root, $data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        /*$filters = $this->filters($data, $filters);*/
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        // On joins that use the "Without" join type we add a WHERE clause in the query string already. So in that case add an AND clause instead
        if (strpos($joinString, 'WHERE') !== false) {
            $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s AND `%s`.custom_object_id='%s' %s", $root, $resultStr, $root, $joinString, $root, $customObject->getId(), $filterString);
        } else {
            $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE `%s`.custom_object_id='%s' %s", $root, $resultStr, $root, $joinString, $root, $customObject->getId(), $filterString);
        }

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    private function newFieldLogicBuilder($data)
    {
        if(empty($data['properties'])) {
            return [];
        }
        $resultStr = [];
        foreach($data['properties'] as $propertyId => $property) {
            $alias = sprintf("%s.%s", $property['uid'], $property['custom_object_internal_name']);
            switch($property['field_type']) {
                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name']);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name']);
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property['field'];
                    if($field['type'] === NumberField::$types['Currency']) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery($alias);
                        $resultStr[] = sprintf($jsonExtract, $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name']);
                    } elseif($field['type'] === NumberField::$types['Unformatted Number']) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery($alias);
                        $resultStr[] = sprintf($jsonExtract, $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name']);
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internal_name'], $property['internal_name'], $property['internal_name'], $property['internal_name']);
                    break;

            }

        }
        return $resultStr;
    }

    private function newJoinLogicBuilder($root, &$data, &$joins = [], $lastJoin = null)
    {
        if(empty($data['joins'])) {
            return [];
        }
        foreach ($data['joins'] as $joinData) {
            $connectedObject = $joinData['connected_object'];
            $connectedProperty = $joinData['connected_property'];
            $joinDirection = $connectedObject['join_direction'];
            $joinType = $joinData['join_type'];
            if($joinType === 'With' && $joinDirection === 'normal_join') {
                $customObject = $this->getEntityManager()->getRepository(CustomObject::class)->find($connectedProperty['field']['customObject']['id']);
                $alias = sprintf("%s.%s", $customObject->getUid(), $connectedProperty['field']['customObject']['internalName']);
                $joins[] = sprintf($this->getJoinQuery(),
                    'INNER JOIN', $alias, $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias
                );
            } elseif ($joinType === 'With/Without' && $joinDirection === 'normal_join') {
                $customObject = $this->getEntityManager()->getRepository(CustomObject::class)->find($connectedProperty['field']['customObject']['id']);
                $alias = sprintf("%s.%s", $customObject->getUid(), $connectedProperty['field']['customObject']['internalName']);
                $joins[] = sprintf($this->getJoinQuery(),
                    'LEFT JOIN', $alias, $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias,
                    $root, $connectedProperty['internalName'], $alias
                );
            } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
                $joins[] = sprintf($this->getWithoutJoinQuery(), $root, $connectedProperty['internalName'], $root, $connectedProperty['internalName']);
            } elseif ($joinType === 'With' && $joinDirection === 'cross_join') {
                $customObject = $this->getEntityManager()->getRepository(CustomObject::class)->find($connectedObject['id']);
                $alias = $customObject->getUid();
                $joins[] = sprintf($this->getCrossJoinQuery(),
                    'INNER JOIN', $alias, $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root
                );
            } elseif ($joinType === 'With/Without' && $joinDirection === 'cross_join') {
                $customObject = $this->getEntityManager()->getRepository(CustomObject::class)->find($connectedObject['id']);
                $alias = $customObject->getUid();
                $joins[] = sprintf($this->getCrossJoinQuery(),
                    'LEFT JOIN', $alias, $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root
                );
            } elseif ($joinType === 'Without' && $joinDirection === 'cross_join') {
                $customObject = $this->getEntityManager()->getRepository(CustomObject::class)->find($connectedObject['id']);
                $alias = $customObject->getUid();
                $joins[] = sprintf($this->getWithoutCrossJoinQuery(),
                    $alias, $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $root,
                    $alias, $connectedProperty['internalName'], $alias, $connectedProperty['internalName']);
            }
        }
        return $joins;
    }

    /**
     * @param $data
     * @param CustomObject $customObject
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportCount($data, CustomObject $customObject)
    {

        $this->data = $data;

        // Setup Joins
        $joins = [];
        $joins = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT count(root.id) as count from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    /**
     * @param $data
     * @param CustomObject $customObject
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportRecordIds($data, CustomObject $customObject)
    {

        $this->data = $data;

        // Setup Joins
        $joins = [];
        $joins = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT root.id from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    /**
     * @param $start
     * @param $length
     * @param $search
     * @param $orders
     * @param $columns
     * @param $propertiesForDatatable
     * @param $customFilters
     * @param CustomObject $customObject
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataTableData($start, $length, $search, $orders, $columns, $propertiesForDatatable, $customFilters, CustomObject $customObject)
    {

        // Setup fields to select
        $resultStr = [];
        foreach($propertiesForDatatable as $property) {

            switch($property->getFieldType()) {

                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property->getField();
                    if($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif($field->isUnformattedNumber()) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
            }
        }

        // Setup Joins
        $joins = [];
        $joins = $this->joins($customFilters, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($customFilters, $filters);
        $filterString = implode(" AND ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        /**
         * @deprecated
         */
        // Joins
        // Don't touch the Join logic unless absolutely necessary. It just works!
/*        $joins = [];
        $joinAlias = 2;
        $previousJoinAlias = 1;
        foreach($customFilters as &$customFilter) {

            if(empty($customFilter['customFilterJoins'])) {
                $customFilter['aliasIndex'] = 1;
                continue;
            }

            $customFilterJoins = $customFilter['customFilterJoins'];

            for($i = 0; $i < count($customFilterJoins); $i++) {

                if($customFilterJoins[$i]['multiple'] === 'true') {
                    $joins[] = sprintf('INNER JOIN record r%s on JSON_SEARCH(r%s.properties->>\'$.%s\', \'one\', r%s.id) IS NOT NULL', $joinAlias, ($i == 0 ? $i + 1 : $previousJoinAlias), $customFilterJoins[$i]['internalName'], $joinAlias);
                } else {
                    $joins[] = sprintf('INNER JOIN record r%s on r%s.properties->>\'$.%s\' = r%s.id', $joinAlias, ($i == 0 ? $i + 1 : $previousJoinAlias), $customFilterJoins[$i]['internalName'], $joinAlias);
                }

                $previousJoinAlias = $joinAlias;
                $joinAlias++;
            }

            $customFilter['aliasIndex'] = ($joinAlias - 1);
        }

        $joinString = implode(" ", $joins);*/

        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);


        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $query .= ' and LOWER(root.properties) LIKE \'%'.strtolower($searchItem).'%\'';
        }

        // Order
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {

                if(isset($order['name'])) {
                    $query .= " ORDER BY LOWER({$order['name']})";
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
            "results"  => $results
        );
    }

    /**
     * @param CustomObject $customObject
     * @param $mergeTag
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function getPropertyFromMergeTag(CustomObject $customObject, $mergeTag) {

        $propertyPathArray = explode(".", $mergeTag);

        foreach($propertyPathArray as $propertyPath) {

            $property = $this->propertyRepository->findOneBy([
                'internalName' => $propertyPath,
                'customObject' => $customObject
            ]);

            // if a property is missing from the given property path we just need to leave this function
            if(!$property) {
                return false;
            }

            /**
             * @see https://stackoverflow.com/questions/53672283/postload-doesnt-work-if-data-are-fetched-with-query-builder
             *
             * PostLoad event occurs for an entity after the entity has been loaded into the current EntityManager
             * from the database or after the refresh operation has been applied to it
             */
            $this->getEntityManager()->refresh($property);

            if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                array_shift($propertyPathArray);
                return $this->getPropertyFromMergeTag($property->getField()->getCustomObject(), implode(".", $propertyPathArray));
            }
            return $property;
        }
    }

    /**
     * This function uses dot annotation to query property values between
     * objects that have relationships.
     *
     * @param $mergeTags
     * @param Record $record
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getPropertiesFromMergeTagsByRecord($mergeTags, Record $record)
    {
        $resultStr = [];
        foreach($mergeTags as $mergeTag) {

            if(!$property = $this->getPropertyFromMergeTag($record->getCustomObject(), $mergeTag)) {
                continue;
            }

            $mergeTagArray = explode(".", $mergeTag);
            array_pop($mergeTagArray);

            $joinPath = !empty($mergeTagArray) ? sprintf('root.%s', implode(".", $mergeTagArray)) : 'root';

            switch($property->getFieldType()) {

                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property->getField();
                    if($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery($joinPath);
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    } elseif($field->isUnformattedNumber()) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery($joinPath);
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    break;
            }
        }

        $joinData = [];
        foreach($mergeTags as $mergeTag) {
            $mergeTagArray = explode(".", $mergeTag);
            array_pop($mergeTagArray);
            $mergeTag = implode(".", $mergeTagArray);
            if(!empty($mergeTag)) {
                $this->setValueByDotNotation($joinData, $mergeTag, []);
            }
        }

        $joins = [];
        $joins = $this->joins($joinData, $joins, 'root');
        $joinString = implode(" ", $joins);

        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.id = '%s'", $resultStr, $joinString, $record->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    /**
     * This function uses property dot annotation to query record values no
     * matter how deeply nested those values are through custom object relations
     *
     * Example:
     * 1. $mergeTag = drop.name
     * 2. $record = a contact record
     * 3. the returned values will be the name of the associated drop and the ID of that associated drop
     *
     * single property value between
     * objects that have relationships.
     *
     * @param $mergeTag
     * @param Record $record
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRecordByPropertyDotAnnotation($mergeTag, Record $record)
    {
        $resultStr = [];
        if(!$property = $this->getPropertyFromMergeTag($record->getCustomObject(), $mergeTag)) {
            return false;
        }

        $mergeTagArray = explode(".", $mergeTag);
        array_pop($mergeTagArray);

        $joinPath = !empty($mergeTagArray) ? sprintf('root.%s', implode(".", $mergeTagArray)) : 'root';

        switch($property->getFieldType()) {

            case FieldCatalog::DATE_PICKER:
                $jsonExtract = $this->getDatePickerQuery($joinPath);
                $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $jsonExtract = $this->getSingleCheckboxQuery($joinPath);
                $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                break;
            case FieldCatalog::NUMBER:
                $field = $property->getField();
                if($field->isCurrency()) {
                    $jsonExtract = $this->getNumberIsCurrencyQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                } elseif($field->isUnformattedNumber()) {
                    $jsonExtract = $this->getNumberIsUnformattedQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                }
                break;
            default:
                $jsonExtract = $this->getDefaultQuery($joinPath);
                $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                break;
        }

        $joinData = [];
        $mergeTagArray = explode(".", $mergeTag);
        array_pop($mergeTagArray);
        $mergeTag = implode(".", $mergeTagArray);
        if(!empty($mergeTag)) {
            $this->setValueByDotNotation($joinData, $mergeTag, []);
        }

        $joins = [];
        $joins = $this->joins($joinData, $joins, 'root');
        $joinString = implode(" ", $joins);

        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT `{$joinPath}`.id, %s from record root %s WHERE root.id = '%s'", $resultStr, $joinString, $record->getId());

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    /**
     * @param $propertiesForDatatable
     * @param $customFilters
     * @param CustomObject $customObject
     * @return string
     */
    public function getCustomFiltersMysqlOnly($propertiesForDatatable, $customFilters, CustomObject $customObject)
    {
        // Setup fields to select
        $resultStr = [];
        foreach($propertiesForDatatable as $property) {

            switch($property->getFieldType()) {

                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property->getField();
                    if($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif($field->isUnformattedNumber()) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery('root');
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;

            }

        }

        // Setup Joins
        $joins = [];
        $joins = $this->joins($customFilters, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($customFilters, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        return $query;
    }

    /**
     * @param $customFilters
     * @param CustomObject $customObject
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTriggerFilterMysqlOnly($customFilters, CustomObject $customObject)
    {
        // setup the join hierarchy
        $joinHierarchy = ['root' => []];
        foreach ($customFilters as $key => &$value) {

            $value['fieldType'] = $value['property']['fieldType'];
            $value['internalName'] = $value['property']['internalName'];

            $newJoin = implode(".", $value['joins']);
            if($this->getValueByDotNotation($newJoin, $joinHierarchy) === false || $this->getValueByDotNotation($newJoin, $joinHierarchy) === null) {
                $this->setValueByDotNotation($joinHierarchy, $newJoin, ['filters' => []]);
                $data = $this->getValueByDotNotation($newJoin, $joinHierarchy);
                $data['filters'][] = $value;
                $this->setValueByDotNotation($joinHierarchy, $newJoin, $data);
            } else {
                $data = $this->getValueByDotNotation($newJoin, $joinHierarchy);
                $data['filters'][] = $value;
                $this->setValueByDotNotation($joinHierarchy, $newJoin, $data);
            }
        }

        // Setup Joins
        $joins = [];
        $joins = $this->joins($joinHierarchy, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($joinHierarchy, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array(
            "results"  => $results
        );
    }

    private function fields($columnOrder)
    {
        $resultStr = [];
        foreach($columnOrder as $column) {

            switch($column['fieldType']) {

                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery(implode(".", $column['joins']));
                    $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery(implode(".", $column['joins']));
                    $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    break;
                case FieldCatalog::NUMBER:
                    $field = $column['field'];

                    if($field['type'] === NumberField::$types['Currency']) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery(implode(".", $column['joins']));
                        $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    } elseif($field['type'] === NumberField::$types['Unformatted Number']) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery(implode(".", $column['joins']));
                        $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery(implode(".", $column['joins']));
                    $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    break;

            }

        }

        return $resultStr;

    }

    private function joins(&$data, &$joins = [], $lastJoin = null)
    {

        foreach ($data as $key => $value) {

            // we aren't setting up filters now so skip those
            if ($key === 'filters') {

                continue;
            } else if (!empty($data[$key]['uID'])) {

                continue;
            } else if ($key === 'root') {

                $this->joins($data[$key], $joins, $key);

            } else {

                $newJoin = "$lastJoin.$key";

                $joins[] = sprintf(
                    $this->getJoinQuery(),
                    $newJoin,
                    $lastJoin,
                    $key,
                    $newJoin,
                    $lastJoin,
                    $key,
                    $newJoin,
                    $lastJoin,
                    $key,
                    $newJoin,
                    $lastJoin,
                    $key,
                    $newJoin
                );

                $this->joins($data[$key], $joins, $newJoin);
            }
        }

        return $joins;

    }

    private function filters(&$data, &$filters = [])
    {
        foreach ($data as $key => $value) {

            // we aren't setting up filters now so skip those
            if ($key !== 'filters' && empty($data[$key]['uID'])) {

                $this->filters($data[$key], $filters);

            } else if ($key === 'filters') {

                foreach($data[$key] as $filter) {

                    // We don't want to set up the OR conditioned filters quite yet
                    if(!empty($filter['referencedFilterPath'])) {
                        continue;
                    }

                    $filters[] = $this->getConditionForReport($filter, implode(".", $filter['joins']));

                }

            }
        }

        return $filters;

    }


    /**
     * @param $customFilter
     * @param $alias
     * @return string
     */
    private function getCondition($customFilter, $alias) {

        $query = '';
        switch($customFilter['fieldType']) {
            case 'number_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', null) < \'%s\' AND r%s.properties->>\'$.%s\' != \'\' AND r%s.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'%s\' AND r%s.properties->>\'$.%s\' != \'\' AND r%s.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'BETWEEN':

                        if($customFilter['field']['type'] === NumberField::$types['Currency']) {
                            $lowValue = number_format((float)$customFilter['low_value'], 2, '.', '');
                            $highValue = number_format((float)$customFilter['high_value'], 2, '.', '');
                        } else {
                            $lowValue = $customFilter['low_value'];
                            $highValue = $customFilter['high_value'];
                        }

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'date_picker_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;

                    case 'BETWEEN':

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }

                        break;

                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'multiple_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
        }

        return $query;
    }

    /**
     * @param $customFilter
     * @param $alias
     * @return string
     */
    private function getConditionForReport($customFilter, $alias) {

        $query = '';
        $andFilters = [];
        switch($customFilter['fieldType']) {
            case 'number_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', null) < \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'BETWEEN':

                        if($customFilter['field']['type'] === NumberField::$types['Currency']) {
                            $lowValue = number_format((float)$customFilter['low_value'], 2, '.', '');
                            $highValue = number_format((float)$customFilter['high_value'], 2, '.', '');
                        } else {
                            $lowValue = $customFilter['low_value'];
                            $highValue = $customFilter['high_value'];
                        }

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'date_picker_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;

                    case 'BETWEEN':

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf('IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }

                        break;

                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'multiple_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf('(`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
        }

        // add any OR conditions

        if(isset($customFilter['orFilters'])) {

            /*$andFilters = [];*/

            foreach($customFilter['orFilters'] as $orFilter) {

                $filterPath = implode(".", $orFilter);

                $filter = $this->getValueByDotNotation($filterPath, $this->data);

                $andFilters[] = $this->getConditionForReport($filter, implode('.', $filter['joins']));
            }

        }

        $query .= implode(' AND ', $andFilters);

        $query = sprintf('(%s)', $query) . PHP_EOL . PHP_EOL;

        return $query;
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

    private function getDatePickerQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$.%s' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$.%s' = '' THEN ""
        ELSE DATE_FORMAT( CAST( JSON_UNQUOTE( `${alias}`.properties->>'$.%s' ) as DATETIME ), '%%m-%%d-%%Y' )
    END AS "%s"
HERE;
    }

    private function getNumberIsCurrencyQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$.%s' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$.%s' = '' THEN ""
        ELSE CAST( `${alias}`.properties->>'$.%s' AS DECIMAL(15,2) ) 
    END AS "%s"
HERE;
    }

    private function getNumberIsUnformattedQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$.%s' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$.%s' = '' THEN ""
        ELSE `${alias}`.properties->>'$.%s'
    END AS "%s"
HERE;
    }

    private function getDefaultQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$.%s' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$.%s' = '' THEN ""
        ELSE `${alias}`.properties->>'$.%s'
    END AS "%s"
HERE;
    }

    private function getSingleCheckboxQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$.%s' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$.%s' = '' THEN ""
        WHEN `${alias}`.properties->>'$.%s' = 'true' THEN "yes"
        WHEN `${alias}`.properties->>'$.%s' = 'false' THEN "no"
        ELSE `${alias}`.properties->>'$.%s'
    END AS "%s"
HERE;
    }

    /**
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     * @return string
     */
    private function getJoinQuery() {
        return <<<HERE

    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    %s record `%s` on `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, ';')

HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutJoinQuery() {
        return <<<HERE
    WHERE (`%s`.properties->>'$.%s' IS NULL OR `%s`.properties->>'$.%s' = '')
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutCrossJoinQuery() {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    LEFT JOIN record `%s` on `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, '$')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, ';')
    WHERE (`%s`.properties->>'$.%s' IS NULL OR `%s`.properties->>'$.%s' = '')
HERE;
    }

    /**
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     * @return string
     */
    private function getCrossJoinQuery() {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    %s record `%s` on `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, '$')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$.%s' REGEXP concat('^', `%s`.id, ';')
HERE;
    }
}
