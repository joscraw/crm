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
        $resultStr = implode(",",$resultStr);
        $query = sprintf("SELECT id, %s from record WHERE custom_object_id='%s'", $resultStr, $customObject->getId());

        // Search
        if(!empty($search['value'])) {
            $searchItem = $search['value'];
            $query .= ' and LOWER(properties) LIKE \'%'.strtolower($searchItem).'%\'';
        }

        // Custom Filters
        // because we the properties column on each record might not contain each possible property due to the fact
        // that new properties can be created after records are created we need to do an IF check cause WHERE/LIKE statements
        // don't work on keys/values (columns) that don't exist
        foreach($customFilters as $customFilter) {

            switch($customFilter['fieldType']) {
                case 'number_field':
                    switch($customFilter['operator']) {
                        case 'EQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') = \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'NEQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') != \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') != \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'LT':

                            if(trim($customFilter['value']) === '') {
                                // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') < \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') < \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'GT':

                            if(trim($customFilter['value']) === '') {
                                // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') > \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') > \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
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
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $customFilter['property'], $customFilter['property'], '', '');
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $customFilter['property'], $customFilter['property'], $lowValue, $highValue);
                            }

                            break;
                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;
                    }
                    break;
                case 'single_line_text_field':
                case 'multi_line_text_field':
                    switch($customFilter['operator']) {
                        case 'EQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $customFilter['property'], $customFilter['property'], strtolower($customFilter['value']));
                            }

                            break;
                        case 'NEQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $customFilter['property'], $customFilter['property'], strtolower($customFilter['value']));
                            }

                            break;
                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;
                    }
                    break;
                case 'date_picker_field':
                    switch($customFilter['operator']) {
                        case 'EQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) = \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'NEQ':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) != \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) != \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'LT':

                            if(trim($customFilter['value']) === '') {
                                // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') < \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;
                        case 'GT':

                            if(trim($customFilter['value']) === '') {
                                // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') > \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['value']);
                            }

                            break;

                        case 'BETWEEN':

                            if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                                // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $customFilter['property'], $customFilter['property'], '', '');
                            } else {
                                $query .= sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) BETWEEN \'%s\' AND \'%s\'', $customFilter['property'], $customFilter['property'], $customFilter['low_value'], $customFilter['high_value']);
                            }

                            break;

                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;
                    }
                    break;
                case 'single_checkbox_field':

                    switch($customFilter['operator']) {
                        case 'IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);
                                if($values == ['0','1']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'%s\' OR IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'%s\'', $customFilter['property'], $customFilter['property'], 'true', $customFilter['property'], $customFilter['property'], 'false');
                                } elseif ($values == ['0']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'%s\'', $customFilter['property'], $customFilter['property'], 'false');
                                } elseif ($values == ['1']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'%s\'', $customFilter['property'], $customFilter['property'], 'true');
                                }
                            }

                            break;
                        case 'NOT_IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) != \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);
                                if($values == ['0','1']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'%s\' AND IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'%s\'', $customFilter['property'], $customFilter['property'], 'true', $customFilter['property'], $customFilter['property'], 'false');
                                } elseif ($values == ['0']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'%s\'', $customFilter['property'], $customFilter['property'], 'false');
                                } elseif ($values == ['1']) {
                                    $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'%s\'', $customFilter['property'], $customFilter['property'], 'true');
                                }
                            }

                            break;
                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;

                    }
                    break;
                case 'dropdown_select_field':
                case 'radio_select_field':

                    switch($customFilter['operator']) {
                        case 'IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);

                                $conditions = [];
                                foreach($values as $value) {
                                    $conditions[] = sprintf(' IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') = \'%s\'', $customFilter['property'], $customFilter['property'], $value);
                                }

                                $query .= ' and' . implode(" OR ", $conditions);
                            }

                            break;
                        case 'NOT_IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) != \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);

                                $conditions = [];
                                foreach($values as $value) {
                                    $conditions[] = sprintf(' IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'\') != \'%s\'', $customFilter['property'], $customFilter['property'], $value);
                                }

                                $query .= ' and' . implode(" AND ", $conditions);
                            }

                            break;
                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;

                    }
                    break;
                case 'multiple_checkbox_field':

                    switch($customFilter['operator']) {
                        case 'IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);

                                $conditions = [];
                                foreach($values as $value) {
                                    $conditions[] = sprintf(' JSON_SEARCH(IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NOT NULL', $customFilter['property'], $customFilter['property'], $value);
                                }

                                $query .= ' and' . implode(" OR ", $conditions);
                            }

                            break;
                        case 'NOT_IN':

                            if(trim($customFilter['value']) === '') {
                                $query .= sprintf(' and IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), null) = \'\'', $customFilter['property'], $customFilter['property']);
                            } else {
                                $values = explode(',', $customFilter['value']);

                                $conditions = [];
                                foreach($values as $value) {
                                    $conditions[] = sprintf(' JSON_SEARCH(IF(properties->>\'$.%s\' IS NOT NULL, LOWER(properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NULL', $customFilter['property'], $customFilter['property'], $value);
                                }

                                $query .= ' and' . implode(" AND ", $conditions);
                            }

                            break;
                        case 'HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is not null', $customFilter['property']);

                            break;
                        case 'NOT_HAS_PROPERTY':

                            $query .= sprintf(' and (properties->>\'$.%s\') is null', $customFilter['property']);

                            break;
                    }
                    break;
            }
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
        WHEN properties->>'$.%s' IS NULL THEN "-" 
        WHEN properties->>'$.%s' = '' THEN ""
        ELSE DATE_FORMAT( CAST( JSON_UNQUOTE( properties->>'$.%s' ) as DATETIME ), '%%m-%%d-%%Y' )
    END AS %s
HERE;
    }

    private function getNumberIsCurrencyQuery() {
        return <<<HERE
    CASE 
        WHEN properties->>'$.%s' IS NULL THEN "-" 
        WHEN properties->>'$.%s' = '' THEN ""
        ELSE CAST( properties->>'$.%s' AS DECIMAL(15,2) ) 
    END AS %s
HERE;
    }

    private function getNumberIsUnformattedQuery() {
        return <<<HERE
    CASE
        WHEN properties->>'$.%s' IS NULL THEN "-" 
        WHEN properties->>'$.%s' = '' THEN ""
        ELSE properties->>'$.%s'
    END AS %s
HERE;
    }

    private function getDefaultQuery() {
        return <<<HERE
    CASE
        WHEN properties->>'$.%s' IS NULL THEN "-" 
        WHEN properties->>'$.%s' = '' THEN ""
        ELSE properties->>'$.%s'
    END AS %s
HERE;
    }

    private function getSingleCheckboxQuery() {
        return <<<HERE
    CASE
        WHEN properties->>'$.%s' IS NULL THEN "-" 
        WHEN properties->>'$.%s' = '' THEN ""
        WHEN properties->>'$.%s' = 'true' THEN "yes"
        WHEN properties->>'$.%s' = 'false' THEN "no"
        ELSE properties->>'$.%s'
    END AS %s
HERE;
    }
}
