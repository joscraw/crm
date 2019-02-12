<?php

namespace App\Repository;

use App\Entity\CustomObject;
use App\Entity\Record;
use App\Model\FieldCatalog;
use App\Model\NumberField;
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


        // Setup filter tree
        function tree(&$customFilters, $allCustomFilters) {

            foreach($customFilters as &$customFilter) {

                $customFiltersForJoin = array_filter($allCustomFilters, function($f) use($customFilter) {
                    return isset($f['customFilterJoin']) && $f['customFilterJoin'] === $customFilter['id'];
                });

                $customFilter['customFiltersForJoin'] = $customFiltersForJoin;

                tree($customFilter['customFiltersForJoin'], $allCustomFilters);
            }
        };

        tree($customFilters, $customFilters);

        // clean duplicates
        function clean(&$customFilters, $allCustomFilters) {

            foreach($customFilters as &$customFilter) {

                $customFiltersForJoin = array_filter($allCustomFilters, function($f) use($customFilter) {
                    return isset($f['customFilterJoin']) && $f['customFilterJoin'] === $customFilter['id'];
                });

                // remove the custom filter joins from the main array
                foreach($customFiltersForJoin as $customFilterForJoin) {

                    $key = array_search($customFilterForJoin['id'], array_column($customFilters, 'id'));

                    if($key !== false) {
                        unset($customFilters[$key]);
                        $customFilters = array_values($customFilters);
                    }

                }
            }
        };

        clean($customFilters, $customFilters);

        $joins = [];
        function joins(&$customFilters, &$joins, $aliasCurrentIndex, $aliasNextIndex) {

            foreach($customFilters as &$customFilter) {

                if($customFilter['fieldType'] === FieldCatalog::CUSTOM_OBJECT) {

                    if($customFilter['field']['multiple'] === 'true') {
                        $joins[] = sprintf('INNER JOIN record r%s on JSON_SEARCH(r%s.properties->>\'$.%s\', \'one\', r%s.id) IS NOT NULL', $aliasNextIndex, $aliasCurrentIndex, $customFilter['internalName'], $aliasNextIndex);
                    } else {
                        $joins[] = sprintf('INNER JOIN record r%s on r%s.properties->>\'$.%s\' = r%s.id', $aliasNextIndex, $aliasCurrentIndex, $customFilter['internalName'], $aliasNextIndex);
                    }


                    $customFilter['aliasIndex'] = $aliasNextIndex;

                    $aliasNextIndex++;
                }

                if(!empty($customFilter['customFiltersForJoin'])) {
                    joins($customFilter['customFiltersForJoin'], $joins, $aliasCurrentIndex + 1, $aliasNextIndex);
                }

            }
        }

        joins($customFilters, $joins, 1, 2);

        $joinString = implode(" ", $joins);


        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT DISTINCT r1.id, %s from record r1 %s WHERE r1.custom_object_id='%s'", $resultStr, $joinString, $customObject->getId());

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $query .= ' and LOWER(r1.properties) LIKE \'%'.strtolower($searchItem).'%\'';
        }

        // Custom Filters
        // because we the properties column on each record might not contain each possible property due to the fact
        // that new properties can be created after records are created we need to do an IF check cause WHERE/LIKE statements
        // don't work on keys/values (columns) that don't exist

        $query = $this->conditions($customFilters, $query);

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
    }

    /**
     * @param $customFilters
     * @param $query
     * @param int $alias
     * @return string
     */
    private function conditions($customFilters, &$query, $alias = 1) {

        foreach($customFilters as $customFilter) {

            if($customFilter['fieldType'] === FieldCatalog::CUSTOM_OBJECT) {

                if(!empty($customFilter['customFiltersForJoin'])) {
                    $this->conditions($customFilter['customFiltersForJoin'], $query, $customFilter['aliasIndex']);
                }

            } else {

                $query = $this->getCondition($customFilter, $query, $alias);
            }
        }

        return $query;
    }

    /**
     * @param $customFilter
     * @param $query
     * @param $alias
     * @return string
     */
    private function getCondition($customFilter, $query, $alias) {

        switch($customFilter['fieldType']) {
            /*case 'custom_object_field':
                foreach($customFilter['customFiltersForJoin'] as $customFilterForJoin) {
                    $query .= $this->getCondition($customFilterForJoin, $query, $customFilter['aliasIndex']);
                }
                break;*/
            case 'number_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'BETWEEN':

                        if($customFilter['numberType'] === NumberField::$types['Currency']) {
                            $lowValue = number_format((float)$customFilter['low_value'], 2, '.', '');
                            $highValue = number_format((float)$customFilter['high_value'], 2, '.', '');
                        } else {
                            $lowValue = $customFilter['low_value'];
                            $highValue = $customFilter['high_value'];
                        }

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'date_picker_field':
                switch($customFilter['operator']) {
                    case 'EQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;

                    case 'BETWEEN':

                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }

                        break;

                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                            }

                            $query .= ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                            }

                            $query .= ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;

                }
                break;
            case 'multiple_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                            }

                            $query .= ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if(trim($customFilter['value']) === '') {
                            $query .= sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                            }

                            $query .= ' and' . implode(" AND ", $conditions);
                        }

                        break;
                    case 'HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is not null', $alias, $customFilter['internalName']);

                        break;
                    case 'NOT_HAS_PROPERTY':

                        $query .= sprintf(' and (r%s.properties->>\'$.%s\') is null', $alias, $customFilter['internalName']);

                        break;
                }
                break;
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

    private function getDefaultQuery() {
        return <<<HERE
    CASE
        WHEN r1.properties->>'$.%s' IS NULL THEN "-" 
        WHEN r1.properties->>'$.%s' = '' THEN ""
        ELSE r1.properties->>'$.%s'
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
