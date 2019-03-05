<?php

namespace App\Repository;

ini_set('xdebug.max_nesting_level', 100000);

use App\Entity\CustomObject;
use App\Entity\Record;
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

        $recordIds = implode(',', $recordIds);

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
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportData($data, CustomObject $customObject)
    {

        $this->data = $data;

        // Setup fields for select
        $resultStr = [];
        $resultStr = $this->fields($data, $resultStr);
        $resultStr = implode(",",$resultStr);

        // Setup Joins
        $joins = [];
        $joins = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters = [];
        $filters = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        /*foreach($customFilters as &$customFilter) {
            $query .= $this->getCondition($customFilter, $customFilter['aliasIndex']);
        }*/


        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' AND %s", $resultStr, $joinString, $customObject->getId(), $filterString);

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
                    $jsonExtract = $this->getDatePickerQuery();
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery();
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property->getField();
                    if($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery();
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif($field->isUnformattedNumber()) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery();
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery();
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    break;

            }

        }


        // Joins
        // Don't touch the Join logic unless absolutely necessary. It just works!
        $joins = [];
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

        $joinString = implode(" ", $joins);

        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT r1.id, %s from record r1 %s WHERE r1.custom_object_id='%s'", $resultStr, $joinString, $customObject->getId());


        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $query .= ' and LOWER(r1.properties) LIKE \'%'.strtolower($searchItem).'%\'';
        }


        // Custom Filters
        foreach($customFilters as &$customFilter) {
            $query .= $this->getCondition($customFilter, $customFilter['aliasIndex']);
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
            "results"  => $results
        );
    }


    private function fields(&$data, &$resultStr = [])
    {

        foreach ($data as $key => $value) {

            // we aren't setting up filters now so skip those
            if ($key === 'filters') {

                continue;
            } else if (!empty($data[$key]['uID'])) {

                // if it contains a uID then it is a field we need to select
                $property = $data[$key];

                switch ($property['fieldType']) {

                    case FieldCatalog::DATE_PICKER:
                        /*                            $jsonExtract = $this->getDatePickerQuery();
                                                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());*/
                        break;
                    default:
                        $jsonExtract = $this->getDefaultQuery(implode(".", $property['joins']));
                        $resultStr[] = sprintf(
                            $jsonExtract,
                            $property['internalName'],
                            $property['internalName'],
                            $property['internalName'],
                            $property['internalName']
                        );
                        break;

                }

            } else {

                    $this->fields($data[$key], $resultStr);
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

                /*$joins[] = sprintf('INNER JOIN record %s on JSON_SEARCH(%s.properties->>\'$.%s\', \'one\', %s.id) IS NOT NULL', "root.$key", $lastJoin, $key, $lastJoin);*/

                $newJoin = "$lastJoin.$key";

                $joins[] = sprintf('INNER JOIN record `%s` on `%s`.properties->>\'$.%s\' = `%s`.id', $newJoin, $lastJoin, $key, $newJoin);

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
                            if($values == ['0','1']) {
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
                            if($values == ['0','1']) {
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
        switch($customFilter['fieldType']) {
            case 'number_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', null) < \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
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
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'date_picker_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;

                    case 'BETWEEN':

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( `%s`.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }

                        break;

                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'multiple_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query = sprintf(' and (`%s`.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
        }

        // add any OR conditions

        if(isset($customFilter['orFilters'])) {

            $andFilters = [];

            foreach($customFilter['orFilters'] as $orFilter) {

                $filterPath = implode(".", $orFilter);

                $filter = $this->getValueByDotNotation($filterPath, $this->data);

                $andFilters[] = $this->getConditionForReport($filter, implode('.', $filter['joins']));
            }

            $query .= ' AND ' . implode(' AND ', $andFilters);

            $query = sprintf('(%s)', $query);
        }

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

    private function getDatePickerQuery() {
        return <<<HERE
    CASE 
        WHEN r1.properties->>'$.%s' IS NULL THEN "-" 
        WHEN r1.properties->>'$.%s' = '' THEN ""
        ELSE DATE_FORMAT( CAST( JSON_UNQUOTE( r1.properties->>'$.%s' ) as DATETIME ), '%%m-%%d-%%Y' )
    END AS "%s"
HERE;
    }

    private function getNumberIsCurrencyQuery() {
        return <<<HERE
    CASE 
        WHEN r1.properties->>'$.%s' IS NULL THEN "-" 
        WHEN r1.properties->>'$.%s' = '' THEN ""
        ELSE CAST( r1.properties->>'$.%s' AS DECIMAL(15,2) ) 
    END AS "%s"
HERE;
    }

    private function getNumberIsUnformattedQuery() {
        return <<<HERE
    CASE
        WHEN r1.properties->>'$.%s' IS NULL THEN "-" 
        WHEN r1.properties->>'$.%s' = '' THEN ""
        ELSE r1.properties->>'$.%s'
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

    private function getSingleCheckboxQuery() {
        return <<<HERE
    CASE
        WHEN r1.properties->>'$.%s' IS NULL THEN "-" 
        WHEN r1.properties->>'$.%s' = '' THEN ""
        WHEN r1.properties->>'$.%s' = 'true' THEN "yes"
        WHEN r1.properties->>'$.%s' = 'false' THEN "no"
        ELSE r1.properties->>'$.%s'
    END AS "%s"
HERE;
    }
}
