<?php

namespace App\Repository;

ini_set('xdebug.max_nesting_level', 100000);

use App\Api\ApiProblemException;
use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Record;
use App\EntityListener\PropertyListener;
use App\Model\FieldCatalog;
use App\Model\Filter\FilterData;
use App\Model\Filter\Join;
use App\Model\NumberField;
use App\Utils\ArrayHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository
{

    use ArrayHelper;
    use RandomStringGenerator;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    public function __construct(ManagerRegistry $registry, PropertyRepository $propertyRepository)
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
     * @param              $search
     * @param CustomObject $allowedCustomObjectToSearch
     * @param              $selectizeAllowedSearchableProperties
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeData($search, CustomObject $allowedCustomObjectToSearch,
                                     $selectizeAllowedSearchableProperties
    ) {

        $jsonExtract = "properties->>'$.\"%s\"' as %s";
        $resultStr   = [];
        foreach ($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",", $resultStr);
        $query     = sprintf("SELECT id, properties %s from record WHERE custom_object_id='%s' AND LOWER(properties) LIKE '%%%s%%'", $resultStr, $allowedCustomObjectToSearch->getId(), strtolower($search));

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }


    /**
     * @param $recordId
     * @param $selectizeAllowedSearchableProperties
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeAllowedSearchablePropertiesById($recordId, $selectizeAllowedSearchableProperties)
    {
        $jsonExtract = "properties->>'$.\"%s\"' as %s";
        $resultStr   = [];
        foreach ($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",", $resultStr);
        $query     = sprintf("SELECT id, properties %s from record WHERE id='%s'", $resultStr, $recordId);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param $recordIds
     * @param $selectizeAllowedSearchableProperties
     *
     * @return mixed[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSelectizeAllowedSearchablePropertiesByArrayOfIds($recordIds,
                                                                        $selectizeAllowedSearchableProperties
    ) {
        $jsonExtract = "properties->>'$.\"%s\"' as %s";
        $resultStr   = [];
        foreach ($selectizeAllowedSearchableProperties as $allowedSearchableProperty) {
            $resultStr[] = sprintf($jsonExtract, $allowedSearchableProperty->getInternalName(), $allowedSearchableProperty->getInternalName());
        }
        $resultStr = empty($resultStr) ? '' : ',' . implode(",", $resultStr);

        $recordIds = "'" . implode("','", $recordIds) . "'";

        $query = sprintf("SELECT id, properties %s from record WHERE id IN (%s)", $resultStr, $recordIds);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param              $data
     * @param CustomObject $customObject
     * @param              $columnOrder
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportData($data, CustomObject $customObject, $columnOrder)
    {

        $this->data = $data;

        // Setup fields for select
        $resultStr = $this->fields($columnOrder);
        $resultStr = implode(",", $resultStr);

        // Setup Joins
        $joins      = [];
        $joins      = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * @param              $data
     * @param CustomObject $customObject
     * @param              $columnOrder
     *
     * @return string
     */
    public function getReportMysqlOnly($data, CustomObject $customObject, $columnOrder)
    {

        $this->data = $data;

        // Setup fields for select
        $resultStr = $this->fields($columnOrder);
        $resultStr = implode(",", $resultStr);

        // Setup Joins
        $joins      = [];
        $joins      = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        return $query;
    }

    /**
     * This function is the new and improved logic for the report builder.
     *
     * @param              $data
     * @param CustomObject $customObject
     * @param bool         $mysqlOnly
     * @param bool         $start
     * @param bool         $length
     * @param bool         $search
     * @param bool         $orders
     * @param bool         $columns
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function newReportLogicBuilder($data, CustomObject $customObject, $mysqlOnly = false, $start = false,
                                          $length = false, $search = false, $orders = false, $columns = false
    ) {
        $this->data = $data;
        $root       = sprintf("%s.%s", $this->generateRandomCharacters(5), $customObject->getInternalName());

        // setup the join aliases
        $this->newJoinAliasBuilder($data);

        // Setup fields for select
        $resultStr = $this->newFieldLogicBuilder($data, $root);
        $resultStr = implode(",", $resultStr);
        $resultStr = !empty($resultStr) ? ', ' . $resultStr : '';

        // Setup Joins
        $joins      = [];
        $joins      = $this->newJoinLogicBuilder($root, $data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->newFilterLogicBuilder($root, $data, $filters);
        $filterString = !empty($filters) ? sprintf("(\n%s)", implode(" OR \n", $filters)) : '';
        $filterString = empty($filters) ? '' : "AND $filterString";

        // Setup Join "Where" Conditionals
        $joinConditionals      = [];
        $joinConditionals      = $this->newJoinConditionalBuilder($root, $data, $joinConditionals);
        $joinConditionalString = !empty($joinConditionals) ? sprintf("(\n%s\n)", implode(" AND \n", $joinConditionals)) : '';

        // On joins that use the "Without" join type we add a WHERE clause in the query string already. So in that case add an AND clause instead
        if (strpos($joinString, 'WHERE') !== false) {
            $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s AND %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        } else {
            $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE \n %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        }

        //  todo possibly remove this and use the above. All I'm doing here is removing the distinct from the query
        if (strpos($joinString, 'WHERE') !== false) {
            $query = sprintf("SELECT `%s`.id %s from record `%s` %s AND %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        } else {
            $query = sprintf("SELECT `%s`.id %s from record `%s` %s WHERE \n %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        }

        // Search
        if (!empty($search['value']) && !empty($data['properties'])) {
            $searches   = [];
            $searchItem = $search['value'];
            foreach ($data['properties'] as $propertyId => $property) {
                $alias      = !empty($property['alias']) ? $property['alias'] : $root;
                $searches[] = sprintf('LOWER(`%s`.properties->>\'$."%s"\') LIKE \'%%%s%%\'', $alias, $property['internalName'], strtolower($searchItem));
            }
            $query .= !empty($searches) ? " AND \n" . sprintf("(\n%s\n)\n", implode("\n OR ", $searches)) : '';
        }

        /**
         * SET THE GROUP BY
         * This ensures that duplicate rows don't get returned with the same root object ID
         * https://stackoverflow.com/questions/23921117/disable-only-full-group-by/23921234
         */
        /*$query .= sprintf(" \nGROUP BY `%s`.id\n", $root);*/
        // todo possibly re-add this. All I'm doing here is removing the group by
        //  When you’re joining you need to think about the distinct option or group by option.
        //  Do we actually want to be adding this in the query?
        //  Like if you write a report where you grab all Events
        //  With Registrations you want to return the same event ID multiple times.
        //  Maybe we make an option to pass up distinct?
        //  Or group by? Or do away with it completely. This would ensure you had something like this:
        //  Event ID 1 Registration 2
        //  Event ID 1 Registration 3
        //  Event ID 1 Registration 4

        // Order
        if ($orders !== false) {
            foreach ($orders as $key => $order) {
                // Orders does not contain the name of the column, but its number,
                // so add the name so we can handle it just like the $columns array
                $orders[$key]['name'] = $columns[$order['column']]['name'];
            }
            foreach ($orders as $key => $order) {
                if (isset($order['name'])) {
                    $query .= "\n ORDER BY LOWER(`{$order['name']}`)";
                }
                $query .= ' ' . $order['dir'];
            }
        }

        // limit
        if ($start !== false && $length !== false) {
            $query .= sprintf("\n LIMIT %s, %s", $start, $length);
        }

        if ($mysqlOnly) {
            return $query;
        }

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * Filter records based on the FilterData object
     *
     * @param FilterData $filterData
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function filterRecords(FilterData $filterData)
    {
        $filterData->generateAliases()
                   ->generateColumnQueries()
                   ->generateFilterCriteria()
                   ->generateFilterQueries()
                   ->generateJoinQueries()
                   ->generateJoinConditionalQueries()
                   ->generateSearchQueries()
                   ->generateOrderQueries()
                   ->validate();

        $query = $filterData->getQuery();

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        if (!$stmt->execute()) {
            throw new ApiProblemException(400, 'Error running query. Contact system administrator');
        }

        if ($filterData->getStatement() === 'SELECT') {
            $results = $stmt->fetchAll();

            return array (
                'count' => count($results),
                "results" => $results,
            );
        } elseif ($filterData->getStatement() === 'UPDATE') {
            return array ("results" => 'Records successfully updated.');
        } else {
            throw new ApiProblemException(400, 'Statement not supported');
        }
    }

    /**
     * This function is the new and improved logic for the report builder.
     *
     * @param              $data
     * @param CustomObject $customObject
     * @param Property     $propertyToUpdate
     * @param              $internalName
     * @param              $value
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function newUpdateLogicBuilder($data, CustomObject $customObject, Property $propertyToUpdate, $value)
    {
        $internalName = $propertyToUpdate->getInternalName();
        $this->data   = $data;
        $root         = sprintf("%s.%s", $this->generateRandomCharacters(5), $customObject->getInternalName());

        // setup the join aliases
        $this->newJoinAliasBuilder($data);

        // Setup Joins
        $joins      = [];
        $joins      = $this->newJoinLogicBuilder($root, $data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->newFilterLogicBuilder($root, $data, $filters);
        $filterString = !empty($filters) ? sprintf("(\n%s)", implode(" OR \n", $filters)) : '';
        $filterString = empty($filters) ? '' : "AND $filterString";

        // Setup Join "Where" Conditionals
        $joinConditionals      = [];
        $joinConditionals      = $this->newJoinConditionalBuilder($root, $data, $joinConditionals);
        $joinConditionalString = !empty($joinConditionals) ? sprintf("(\n%s\n)", implode(" AND \n", $joinConditionals)) : '';

        $query = sprintf("UPDATE record `%s` %s SET `%s`.properties = JSON_SET(`%s`.properties, '$.\"%s\"', '%s') \n WHERE %s %s", $root, $joinString, $root, $root, $internalName, $value, $joinConditionalString, $filterString);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
    }


    /**
     * This function is the new and improved logic for the report builder and just returns the count.
     *
     * @param              $data
     * @param CustomObject $customObject
     * @param bool         $mysqlOnly
     * @param bool         $start
     * @param bool         $length
     * @param bool         $search
     * @param bool         $orders
     * @param bool         $columns
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function newReportLogicBuilderCount($data, CustomObject $customObject, $mysqlOnly = false, $start = false,
                                               $length = false, $search = false, $orders = false, $columns = false
    ) {
        $this->data = $data;
        $root       = sprintf("%s.%s", $this->generateRandomCharacters(5), $customObject->getInternalName());

        // setup the join aliases
        $this->newJoinAliasBuilder($data);

        // Setup fields for select
        $resultStr = '';

        // Setup Joins
        $joins      = [];
        $joins      = $this->newJoinLogicBuilder($root, $data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->newFilterLogicBuilder($root, $data, $filters);
        $filterString = !empty($filters) ? sprintf("(\n%s)", implode(" OR \n", $filters)) : '';
        $filterString = empty($filters) ? '' : "AND $filterString";

        // Setup Join "Where" Conditionals
        $joinConditionals      = [];
        $joinConditionals      = $this->newJoinConditionalBuilder($root, $data, $joinConditionals);
        $joinConditionalString = !empty($joinConditionals) ? sprintf("(\n%s\n)", implode(" AND \n", $joinConditionals)) : '';

        // On joins that use the "Without" join type we add a WHERE clause in the query string already. So in that case add an AND clause instead
        /*        if (strpos($joinString, 'WHERE') !== false) {
                    $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s AND %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
                } else {
                    $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE \n %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
                }*/

        //  todo possibly remove this and use the above. All I'm doing here is removing the distinct from the query
        if (strpos($joinString, 'WHERE') !== false) {
            $query = sprintf("SELECT `%s`.id %s from record `%s` %s AND %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        } else {
            $query = sprintf("SELECT `%s`.id %s from record `%s` %s WHERE \n %s \n %s", $root, $resultStr, $root, $joinString, $joinConditionalString, $filterString);
        }

        // Search
        if (!empty($search['value']) && !empty($data['properties'])) {
            $searches   = [];
            $searchItem = $search['value'];
            foreach ($data['properties'] as $propertyId => $property) {
                $alias      = !empty($property['alias']) ? $property['alias'] : $root;
                $searches[] = sprintf('LOWER(`%s`.properties->>\'$."%s"\') LIKE \'%%%s%%\'', $alias, $property['internalName'], strtolower($searchItem));
            }
            $query .= !empty($searches) ? " AND \n" . sprintf("(\n%s\n)\n", implode("\n OR ", $searches)) : '';
        }

        /**
         * SET THE GROUP BY
         * This ensures that duplicate rows don't get returned with the same root object ID
         * https://stackoverflow.com/questions/23921117/disable-only-full-group-by/23921234
         */
        /*$query .= sprintf(" \nGROUP BY `%s`.id\n", $root);*/
        // todo possibly re-add this. All I'm doing here is removing the group by
        //  When you’re joining you need to think about the distinct option or group by option.
        //  Do we actually want to be adding this in the query?
        //  Like if you write a report where you grab all Events
        //  With Registrations you want to return the same event ID multiple times.
        //  Maybe we make an option to pass up distinct?
        //  Or group by? Or do away with it completely. This would ensure you had something like this:
        //  Event ID 1 Registration 2
        //  Event ID 1 Registration 3
        //  Event ID 1 Registration 4

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return count($results);
    }

    /**
     * This function loops through all the joins and creates aliases for each join
     * and then attaches the properties and filters to their corresponding aliases.
     *
     * @param $data
     *
     * @return array|void
     */
    private function newJoinAliasBuilder(&$data)
    {
        if (empty($data['joins'])) {
            return;
        }
        // configure the aliases
        foreach ($data['joins'] as &$joinData) {
            if (empty($joinData['relationship_property'])) {
                continue;
            }
            $joinDirection = $joinData['relationship_property']['join_direction'];
            $joinType      = $joinData['join_type'];
            $randomString  = $this->generateRandomCharacters(5);
            if ($joinType === 'With' && $joinDirection === 'normal_join' || $joinType === 'With/Without' && $joinDirection === 'normal_join') {
                $alias             = sprintf("%s.%s", $randomString, $joinData['relationship_property']['field']['customObject']['internalName']);
                $joinData['alias'] = $alias;
                // add the alias to each property
                if (!empty($data['properties'])) {
                    foreach ($data['properties'] as $propertyId => &$property) {
                        if ($joinData['relationship_property']['field']['customObject']['id'] == $property['custom_object_id']) {
                            $property['alias'] = $alias;
                        }
                    }
                }
                // add each alias to each filter
                if (!empty($data['filters'])) {
                    foreach ($data['filters'] as &$filter) {
                        if ($joinData['relationship_property']['field']['customObject']['id'] == $filter['custom_object_id']) {
                            $filter['alias'] = $alias;
                        }
                    }
                }
            } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
                // do nothing
            } elseif ($joinType === 'With' && $joinDirection === 'cross_join' ||
                $joinType === 'With/Without' && $joinDirection === 'cross_join' ||
                $joinType === 'Without' && $joinDirection === 'cross_join') {
                $alias             = sprintf("%s.%s", $randomString, $joinData['relationship_property']['custom_object_internal_name']);
                $joinData['alias'] = $alias;
                if (!empty($data['properties'])) {
                    foreach ($data['properties'] as $propertyId => &$property) {
                        if ($joinData['relationship_property']['custom_object_id'] == $property['custom_object_id']) {
                            $property['alias'] = $alias;
                        }
                    }
                }
                // add each alias to each filter
                if (!empty($data['filters'])) {
                    foreach ($data['filters'] as &$filter) {
                        if ($joinData['relationship_property']['custom_object_id'] == $filter['custom_object_id']) {
                            $filter['alias'] = $alias;
                        }
                    }
                }
            }
        }
    }

    /**
     * This function loops through all the joins and creates the Where conditionals needed
     *
     * @param $root
     * @param $data
     * @param $joinConditionals
     *
     * @return array
     */
    private function newJoinConditionalBuilder($root, &$data, &$joinConditionals)
    {
        if (empty($data['joins'])) {
            return;
        }

        // configure the aliases
        foreach ($data['joins'] as &$joinData) {

            if (empty($joinData['relationship_property']) || empty($joinData['relationship_property'])) {
                $joinConditionals[] = sprintf("`%s`.custom_object_id = %s", $root, $data['selectedCustomObject']['id']);
                continue;
            }
            $joinDirection = $joinData['relationship_property']['join_direction'];
            $joinType      = $joinData['join_type'];
            $alias         = $joinData['alias'];

            $skipJoinCondition = ($joinType === 'Without' && $joinDirection === 'normal_join') ||
                ($joinType === 'With/Without');

            if ($skipJoinCondition) {
                continue;
            }

            if ($joinType === 'With' && $joinDirection === 'normal_join' || $joinType === 'With/Without' && $joinDirection === 'normal_join') {
                $joinConditionals[] = sprintf("`%s`.custom_object_id = %s", $alias, $joinData['relationship_property']['field']['customObject']['id']);
            } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
                // do nothing
            } elseif ($joinType === 'With' && $joinDirection === 'cross_join' ||
                $joinType === 'With/Without' && $joinDirection === 'cross_join' ||
                $joinType === 'Without' && $joinDirection === 'cross_join') {
                $joinConditionals[] = sprintf("`%s`.custom_object_id = %s", $alias, $joinData['relationship_property']['custom_object_id']);
            }
        }

        return $joinConditionals;
    }

    /**
     * This function sets up the property fields we are querying
     *
     * @param $data
     * @param $root
     *
     * @return array
     */
    private function newFieldLogicBuilder($data, $root)
    {
        if (empty($data['properties'])) {
            return [];
        }
        $resultStr = [];
        foreach ($data['properties'] as $propertyId => $property) {
            $alias      = !empty($property['alias']) ? $property['alias'] : $root;
            $columnName = $property['column_label'];
            switch ($property['fieldType']) {
                case FieldCatalog::DATE_PICKER:
                    $jsonExtract = $this->getDatePickerQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internalName'], $property['internalName'], $property['internalName'], $columnName);
                    break;
                case FieldCatalog::SINGLE_CHECKBOX:
                    $jsonExtract = $this->getSingleCheckboxQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internalName'], $property['internalName'], $property['internalName'], $property['internalName'], $property['internalName'], $columnName);
                    break;
                case FieldCatalog::NUMBER:
                    $field = $property['field'];
                    if ($field['type'] === NumberField::$types['Currency']) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery($alias);
                        $resultStr[] = sprintf($jsonExtract, $property['internalName'], $property['internalName'], $property['internalName'], $columnName);
                    } elseif ($field['type'] === NumberField::$types['Unformatted Number']) {
                        $jsonExtract = $this->getNumberIsUnformattedQuery($alias);
                        $resultStr[] = sprintf($jsonExtract, $property['internalName'], $property['internalName'], $property['internalName'], $columnName);
                    }
                    break;
                default:
                    $jsonExtract = $this->getDefaultQuery($alias);
                    $resultStr[] = sprintf($jsonExtract, $property['internalName'], $property['internalName'], $property['internalName'], $columnName);
                    break;

            }

        }

        return $resultStr;
    }

    /**
     * This function sets up the joins for the query
     *
     * @param       $root
     * @param       $data
     * @param array $joins
     * @param null  $lastJoin
     *
     * @return array
     */
    private function newJoinLogicBuilder($root, &$data, &$joins = [], $lastJoin = null)
    {
        if (empty($data['joins'])) {
            return [];
        }
        foreach ($data['joins'] as $joinData) {
            if (empty($joinData['relationship_property'])) {
                continue;
            }
            /*if(empty($joinData['connected_object']) || empty($joinData['connected_property'])) {
                continue;
            }*/

            // if the join has a parent connection don't add the join here. It will be added below as a nested join
            if (!empty($joinData['hasParentConnection'])) {
                continue;
            }
            // add the main connections (joins)
            $joins[] = $this->calculateJoin($joinData, $root);
            // add the child connections (joins)

            $this->childConnections($joinData, $data, $joins);

        }

        return $joins;
    }

    private function childConnections($joinData, $data, &$joins)
    {
        if (!empty($joinData['childConnections'])) {
            foreach ($joinData['childConnections'] as $uid => $childConnection) {
                $childConnection['alias'] = $data['joins'][$uid]['alias'];
                // set the new root equal to the parent alias so the next join references the correct alias
                $root    = $joinData['alias'];
                $joins[] = $this->calculateJoin($childConnection, $root);

                if (!empty($childConnection['childConnections'])) {
                    $this->childConnections($childConnection, $data, $joins);
                }
            }
        }
    }

    private function calculateJoin($joinData, $root)
    {
        $relationshipProperty = $joinData['relationship_property'];
        $joinDirection        = $joinData['relationship_property']['join_direction'];
        $joinType             = $joinData['join_type'];
        $alias                = !empty($joinData['alias']) ? $joinData['alias'] : $root;
        $query                = '';
        if ($joinType === 'With' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getJoinQuery(),
                'INNER JOIN', $alias, $root, $joinData['relationship_property']['internalName'], $alias,
                $root, $joinData['relationship_property']['internalName'], $alias,
                $root, $joinData['relationship_property']['internalName'], $alias,
                $root, $joinData['relationship_property']['internalName'], $alias
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getWithOrWithoutJoinQuery(),
                $alias, $root, $joinData['relationship_property']['internalName'], $alias, $alias, $joinData['relationship_property']['field']['customObject']['id'],
                $root, $joinData['relationship_property']['internalName'], $alias, $alias, $joinData['relationship_property']['field']['customObject']['id'],
                $root, $joinData['relationship_property']['internalName'], $alias, $alias, $joinData['relationship_property']['field']['customObject']['id'],
                $root, $joinData['relationship_property']['internalName'], $alias, $alias, $joinData['relationship_property']['field']['customObject']['id']
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getWithoutJoinQuery(), $root, $joinData['relationship_property']['internalName'], $root, $joinData['relationship_property']['internalName']);
        } elseif ($joinType === 'With' && $joinDirection === 'cross_join') {
            $query = sprintf($this->getCrossJoinQuery(),
                'INNER JOIN', $alias, $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'cross_join') {

            $query = sprintf($this->getWithOrWithoutCrossJoinQuery(),
                $alias, $alias, $joinData['relationship_property']['internalName'], $root, $alias, $joinData['relationship_property']['custom_object_id'],
                $alias, $joinData['relationship_property']['internalName'], $root, $alias, $joinData['relationship_property']['custom_object_id'],
                $alias, $joinData['relationship_property']['internalName'], $root, $alias, $joinData['relationship_property']['custom_object_id'],
                $alias, $joinData['relationship_property']['internalName'], $root, $alias, $joinData['relationship_property']['custom_object_id']
            );

        } elseif ($joinType === 'Without' && $joinDirection === 'cross_join') {
            $query = sprintf($this->getWithoutCrossJoinQuery(),
                $alias, $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $root,
                $alias, $joinData['relationship_property']['internalName'], $alias, $joinData['relationship_property']['internalName']);
        }

        return $query;
    }

    /**
     * This function sets up the filters for the query
     *
     * @param       $root
     * @param       $data
     * @param array $filters
     *
     * @return array
     */
    private function newFilterLogicBuilder($root, &$data, &$filters = [])
    {
        if (empty($data['filters'])) {
            return [];
        }
        foreach ($data['filters'] as $filter) {
            // if the filter has a parent filter don't add it here. It will be added as an AND conditional below
            if (!empty($filter['hasParentFilter'])) {
                continue;
            }
            $alias     = !empty($filter['alias']) ? $filter['alias'] : $root;
            $filters[] = $this->getConditionForReport($filter, $alias, $data);
        }

        return $filters;
    }


    /**
     * @param              $data
     * @param CustomObject $customObject
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportCount($data, CustomObject $customObject)
    {

        $this->data = $data;

        // Setup Joins
        $joins      = [];
        $joins      = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT count(root.id) as count from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * @param              $data
     * @param CustomObject $customObject
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportRecordIds($data, CustomObject $customObject)
    {

        $this->data = $data;

        // Setup Joins
        $joins      = [];
        $joins      = $this->joins($data, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($data, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT root.id from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * @param              $start
     * @param              $length
     * @param              $search
     * @param              $orders
     * @param              $columns
     * @param              $propertiesForDatatable
     * @param              $customFilters
     * @param CustomObject $customObject
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataTableData($start, $length, $search, $orders, $columns, $propertiesForDatatable,
                                     $customFilters, CustomObject $customObject
    ) {

        // Setup fields to select
        $resultStr = [];
        foreach ($propertiesForDatatable as $property) {

            switch ($property->getFieldType()) {

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
                    if ($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif ($field->isUnformattedNumber()) {
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
        $joins      = [];
        $joins      = $this->joins($customFilters, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($customFilters, $filters);
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

        $resultStr = implode(",", $resultStr);
        $query     = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);


        // Search
        if (!empty($search['value'])) {
            $searchItem = $search['value'];
            $query      .= ' and LOWER(root.properties) LIKE \'%' . strtolower($searchItem) . '%\'';
        }

        // Order
        foreach ($orders as $key => $order) {
            // Orders does not contain the name of the column, but its number,
            // so add the name so we can handle it just like the $columns array
            $orders[$key]['name'] = $columns[$order['column']]['name'];
        }

        foreach ($orders as $key => $order) {

            if (isset($order['name'])) {
                $query .= " ORDER BY LOWER({$order['name']})";
            }

            $query .= ' ' . $order['dir'];
        }


        // limit
        $query .= sprintf(' LIMIT %s, %s', $start, $length);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * @param              $start
     * @param              $length
     * @param              $search
     * @param              $orders
     * @param              $columns
     * @param              $propertiesForDatatable
     * @param              $customFilters
     * @param CustomObject $customObject
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDataTableDataCount($start, $length, $search, $orders, $columns, $propertiesForDatatable,
                                          $customFilters, CustomObject $customObject
    ) {

        // Setup fields to select
        $resultStr = [];
        foreach ($propertiesForDatatable as $property) {

            switch ($property->getFieldType()) {

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
                    if ($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif ($field->isUnformattedNumber()) {
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
        $joins      = [];
        $joins      = $this->joins($customFilters, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($customFilters, $filters);
        $filterString = implode(" AND ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $resultStr = implode(",", $resultStr);
        $query     = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        // Search
        if (!empty($search['value'])) {
            $searchItem = $search['value'];
            $query      .= ' and LOWER(root.properties) LIKE \'%' . strtolower($searchItem) . '%\'';
        }

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return count($results);
    }


    /**
     * @param CustomObject $customObject
     * @param              $mergeTag
     *
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function getPropertyFromMergeTag(CustomObject $customObject, $mergeTag)
    {

        $propertyPathArray = explode(".", $mergeTag);

        foreach ($propertyPathArray as $propertyPath) {

            $property = $this->propertyRepository->findOneBy([
                'internalName' => $propertyPath,
                'customObject' => $customObject,
            ]);

            // if a property is missing from the given property path we just need to leave this function
            if (!$property) {
                return false;
            }

            /**
             * @see https://stackoverflow.com/questions/53672283/postload-doesnt-work-if-data-are-fetched-with-query-builder
             *
             * PostLoad event occurs for an entity after the entity has been loaded into the current EntityManager
             * from the database or after the refresh operation has been applied to it
             */
            $this->getEntityManager()->refresh($property);

            if ($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
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
     * @param        $mergeTags
     * @param Record $record
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getPropertiesFromMergeTagsByRecord($mergeTags, Record $record)
    {
        $resultStr = [];
        foreach ($mergeTags as $mergeTag) {

            if (!$property = $this->getPropertyFromMergeTag($record->getCustomObject(), $mergeTag)) {
                continue;
            }

            $mergeTagArray = explode(".", $mergeTag);
            array_pop($mergeTagArray);

            $joinPath = !empty($mergeTagArray) ? sprintf('root.%s', implode(".", $mergeTagArray)) : 'root';

            switch ($property->getFieldType()) {

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
                    if ($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery($joinPath);
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                    } elseif ($field->isUnformattedNumber()) {
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
        foreach ($mergeTags as $mergeTag) {
            $mergeTagArray = explode(".", $mergeTag);
            array_pop($mergeTagArray);
            $mergeTag = implode(".", $mergeTagArray);
            if (!empty($mergeTag)) {
                $this->setValueByDotNotation($joinData, $mergeTag, []);
            }
        }

        $joins      = [];
        $joins      = $this->joins($joinData, $joins, 'root');
        $joinString = implode(" ", $joins);

        $resultStr = implode(",", $resultStr);
        $query     = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.id = '%s'", $resultStr, $joinString, $record->getId());

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
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
     * @param        $mergeTag
     * @param Record $record
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getRecordByPropertyDotAnnotation($mergeTag, Record $record)
    {
        $resultStr = [];
        if (!$property = $this->getPropertyFromMergeTag($record->getCustomObject(), $mergeTag)) {
            return false;
        }

        $mergeTagArray = explode(".", $mergeTag);
        array_pop($mergeTagArray);

        $joinPath = !empty($mergeTagArray) ? sprintf('root.%s', implode(".", $mergeTagArray)) : 'root';

        switch ($property->getFieldType()) {

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
                if ($field->isCurrency()) {
                    $jsonExtract = $this->getNumberIsCurrencyQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                } elseif ($field->isUnformattedNumber()) {
                    $jsonExtract = $this->getNumberIsUnformattedQuery($joinPath);
                    $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                }
                break;
            default:
                $jsonExtract = $this->getDefaultQuery($joinPath);
                $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $mergeTag);
                break;
        }

        $joinData      = [];
        $mergeTagArray = explode(".", $mergeTag);
        array_pop($mergeTagArray);
        $mergeTag = implode(".", $mergeTagArray);
        if (!empty($mergeTag)) {
            $this->setValueByDotNotation($joinData, $mergeTag, []);
        }

        $joins      = [];
        $joins      = $this->joins($joinData, $joins, 'root');
        $joinString = implode(" ", $joins);

        $resultStr = implode(",", $resultStr);
        $query     = sprintf("SELECT DISTINCT `{$joinPath}`.id, %s from record root %s WHERE root.id = '%s'", $resultStr, $joinString, $record->getId());

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    /**
     * @param              $propertiesForDatatable
     * @param              $customFilters
     * @param CustomObject $customObject
     *
     * @return string
     */
    public function getCustomFiltersMysqlOnly($propertiesForDatatable, $customFilters, CustomObject $customObject)
    {
        // Setup fields to select
        $resultStr = [];
        foreach ($propertiesForDatatable as $property) {

            switch ($property->getFieldType()) {

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
                    if ($field->isCurrency()) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery('root');
                        $resultStr[] = sprintf($jsonExtract, $property->getInternalName(), $property->getInternalName(), $property->getInternalName(), $property->getInternalName());
                    } elseif ($field->isUnformattedNumber()) {
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
        $joins      = [];
        $joins      = $this->joins($customFilters, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($customFilters, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $resultStr = implode(",", $resultStr);
        $query     = sprintf("SELECT DISTINCT root.id, %s from record root %s WHERE root.custom_object_id='%s' %s", $resultStr, $joinString, $customObject->getId(), $filterString);

        return $query;
    }

    /**
     * @param              $customFilters
     * @param CustomObject $customObject
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTriggerFilterMysqlOnly($customFilters, CustomObject $customObject)
    {
        // setup the join hierarchy
        $joinHierarchy = ['root' => []];
        foreach ($customFilters as $key => &$value) {

            $value['fieldType']    = $value['property']['fieldType'];
            $value['internalName'] = $value['property']['internalName'];

            $newJoin = implode(".", $value['joins']);
            if ($this->getValueByDotNotation($newJoin, $joinHierarchy) === false || $this->getValueByDotNotation($newJoin, $joinHierarchy) === null) {
                $this->setValueByDotNotation($joinHierarchy, $newJoin, ['filters' => []]);
                $data              = $this->getValueByDotNotation($newJoin, $joinHierarchy);
                $data['filters'][] = $value;
                $this->setValueByDotNotation($joinHierarchy, $newJoin, $data);
            } else {
                $data              = $this->getValueByDotNotation($newJoin, $joinHierarchy);
                $data['filters'][] = $value;
                $this->setValueByDotNotation($joinHierarchy, $newJoin, $data);
            }
        }

        // Setup Joins
        $joins      = [];
        $joins      = $this->joins($joinHierarchy, $joins);
        $joinString = implode(" ", $joins);

        // Setup Filters
        $filters      = [];
        $filters      = $this->filters($joinHierarchy, $filters);
        $filterString = implode(" OR ", $filters);

        $filterString = empty($filters) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT root.id from record root %s WHERE root.custom_object_id='%s' %s", $joinString, $customObject->getId(), $filterString);

        $em   = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return array (
            "results" => $results,
        );
    }

    private function fields($columnOrder)
    {
        $resultStr = [];
        foreach ($columnOrder as $column) {

            switch ($column['fieldType']) {

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

                    if ($field['type'] === NumberField::$types['Currency']) {
                        $jsonExtract = $this->getNumberIsCurrencyQuery(implode(".", $column['joins']));
                        $resultStr[] = sprintf($jsonExtract, $column['internalName'], $column['internalName'], $column['internalName'], $column['internalName']);
                    } elseif ($field['type'] === NumberField::$types['Unformatted Number']) {
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
            } else {
                if (!empty($data[$key]['uID'])) {

                    continue;
                } else {
                    if ($key === 'root') {

                        $this->joins($data[$key], $joins, $key);

                    } else {

                        $newJoin = "$lastJoin.$key";

                        $joins[] = sprintf(
                            $this->getJoinQuery(),
                            'INNER JOIN',
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

            } else {
                if ($key === 'filters') {

                    foreach ($data[$key] as $filter) {

                        // We don't want to set up the OR conditioned filters quite yet
                        if (!empty($filter['referencedFilterPath'])) {
                            continue;
                        }

                        $filters[] = $this->getConditionForReport($filter, implode(".", $filter['joins']));

                    }

                }
            }
        }

        return $filters;
    }


    /**
     * @param $customFilter
     * @param $alias
     *
     * @return string
     */
    private function getCondition($customFilter, $alias)
    {

        $query = '';
        switch ($customFilter['fieldType']) {
            case 'number_field':
                switch ($customFilter['operator']) {
                    case 'EQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');
                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', null) < \'%s\' AND r%s.properties->>\'$.%s\' != \'\' AND r%s.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':

                        $value = number_format((float)$customFilter['value'], 2, '.', '');

                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, r%s.properties->>\'$.%s\', \'\') > \'%s\' AND r%s.properties->>\'$.%s\' != \'\' AND r%s.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'BETWEEN':

                        if ($customFilter['field']['type'] === NumberField::$types['Currency']) {
                            $lowValue  = number_format((float)$customFilter['low_value'], 2, '.', '');
                            $highValue = number_format((float)$customFilter['high_value'], 2, '.', '');
                        } else {
                            $lowValue  = $customFilter['low_value'];
                            $highValue = $customFilter['high_value'];
                        }

                        if (trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
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
                switch ($customFilter['operator']) {
                    case 'EQ':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }

                        break;
                    case 'NEQ':

                        if (trim($customFilter['value']) === '') {
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
                switch ($customFilter['operator']) {
                    case 'EQ':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'NEQ':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'LT':

                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) < \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;
                    case 'GT':

                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $query = sprintf(' and IF(DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), DATE_FORMAT( CAST( JSON_UNQUOTE( r%s.properties->>\'$.%s\' ) as DATETIME ), \'%%m-%%d-%%Y\' ), null) > \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $customFilter['value']);
                        }

                        break;

                    case 'BETWEEN':

                        if (trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
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

                switch ($customFilter['operator']) {
                    case 'IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if ($values == ['0', '1'] || $values == ['1', '0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['0']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'false');
                            } elseif ($values == ['1']) {
                                $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], 'true');
                            }
                        }

                        break;
                    case 'NOT_IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if ($values == ['0', '1'] || $values == ['1', '0']) {
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

                switch ($customFilter['operator']) {
                    case 'IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf(' IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach ($values as $value) {
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

                switch ($customFilter['operator']) {
                    case 'IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf(' JSON_SEARCH(IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), \'[]\'), \'one\', \'%s\') IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }

                            $query = ' and' . implode(" OR ", $conditions);
                        }

                        break;
                    case 'NOT_IN':

                        if (trim($customFilter['value']) === '') {
                            $query = sprintf(' and IF(r%s.properties->>\'$.%s\' IS NOT NULL, LOWER(r%s.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);

                            $conditions = [];
                            foreach ($values as $value) {
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
     * @param      $customFilter
     * @param      $alias
     * @param null $data
     * @param bool $isChildFilter
     *
     * @return string
     */
    private function getConditionForReport($customFilter, $alias, $data = null, $isChildFilter = false)
    {

        $query      = '';
        $andFilters = [];
        switch ($customFilter['fieldType']) {
            case 'number_field':
                switch ($customFilter['operator']) {
                    case 'EQ':
                        $value = str_replace(',', '', $customFilter['value']);
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':
                        $value = str_replace(',', '', $customFilter['value']);
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':
                        $value = str_replace(',', '', $customFilter['value']);
                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', null) < \'%s\' AND `%s`.properties->>\'$."%s"\' != \'\' AND `%s`.properties->>\'$."%s"\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':
                        $value = str_replace(',', '', $customFilter['value']);
                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') > \'%s\' AND `%s`.properties->>\'$."%s"\' != \'\' AND `%s`.properties->>\'$."%s"\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }
                        break;
                    case 'BETWEEN':
                        $lowValue  = str_replace(',', '', $customFilter['low_value']);
                        $highValue = str_replace(',', '', $customFilter['high_value']);
                        if (trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch ($customFilter['operator']) {
                    case 'EQ':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }
                        break;
                    case 'NEQ':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($customFilter['value']));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'date_picker_field':
                switch ($customFilter['operator']) {
                    case 'EQ':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$."%s"\', \'%%m/%%d/%%Y\') = STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'NEQ':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$."%s"\', \'%%m/%%d/%%Y\') != STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'LT':
                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$."%s"\', \'%%m/%%d/%%Y\') < STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'GT':
                        if (trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$."%s"\', \'%%m/%%d/%%Y\') > STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'BETWEEN':
                        if (trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, `%s`.properties->>\'$."%s"\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$."%s"\', \'%%m/%%d/%%Y\') BETWEEN STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\') AND STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'single_checkbox_field':

                switch ($customFilter['operator']) {
                    case 'IN':

                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if ($values == ['0', '1'] || $values == ['1', '0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'%s\' OR IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'%s\')', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1');
                            }
                        }
                        break;
                    case 'NOT_IN':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if ($values == ['0', '1'] || $values == ['1', '0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'%s\' AND IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'%s\')', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1');
                            }
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                switch ($customFilter['operator']) {
                    case 'IN':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values     = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values     = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'multiple_checkbox_field':
                switch ($customFilter['operator']) {
                    case 'IN':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values     = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if (trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values     = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach ($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$."%s"\' IS NOT NULL, LOWER(`%s`.properties->>\'$."%s"\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is not null AND `%s`.properties->>\'$."%s"\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$."%s"\' is null OR `%s`.properties->>\'$."%s"\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
        }

        // add the child filters (AND conditionals)
        if (!empty($customFilter['childFilters'])) {
            foreach ($customFilter['childFilters'] as $uid => $childFilter) {
                $alias        = !empty($data['filters'][$uid]['alias']) ? $data['filters'][$uid]['alias'] : $alias;
                $andFilters[] = $this->getConditionForReport($childFilter, $alias, $data, true);
            }
        }
        $query .= !empty($andFilters) ? implode(" AND ", $andFilters) : '';
        if (!$isChildFilter) {
            $query = sprintf("(\n%s\n)", $query) . PHP_EOL;
        }

        return $query;
    }

    /**
     * @param CustomObject $customObject
     *
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

    private function getDatePickerQuery($alias = 'r1')
    {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getNumberIsCurrencyQuery($alias = 'r1')
    {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE CAST( `${alias}`.properties->>'$."%s"' AS DECIMAL(15,2) ) 
    END AS "%s"
HERE;
    }

    private function getNumberIsUnformattedQuery($alias = 'r1')
    {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getDefaultQuery($alias = 'r1')
    {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getSingleCheckboxQuery($alias = 'r1')
    {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        WHEN `${alias}`.properties->>'$."%s"' = '1' THEN "yes"
        WHEN `${alias}`.properties->>'$."%s"' = '0' THEN "no"
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    /**
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     *
     * @return string
     */
    private function getJoinQuery()
    {
        return <<<HERE

    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    %s record `%s` on `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, ';')

HERE;
    }

    /**
     * Normal Join Looking for records without a match
     *
     * @return string
     */
    private function getWithoutJoinQuery()
    {
        return <<<HERE
    WHERE (`%s`.properties->>'$."%s"' IS NULL OR `%s`.properties->>'$."%s"' = '')
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     *
     * @return string
     */
    private function getWithoutCrossJoinQuery()
    {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    LEFT JOIN record `%s` on `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, '$')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, ';')
    WHERE (`%s`.properties->>'$."%s"' IS NULL OR `%s`.properties->>'$."%s"' = '')
>>>>>>> conversations
HERE;
    }

    /**
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     *
     * @return string
     */
    private function getCrossJoinQuery()
    {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    %s record `%s` on `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, '$')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR `%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, ';')
HERE;
    }
}
