<?php

namespace App\Model\Filter;

use App\Api\ApiProblemException;
use App\Entity\CustomObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

class FilterData extends AbstractFilter
{
    /**
     * @var string Flag for whether or not query bindings should be used.
     * Not having this on could potentially leave queries open for SQL Injection
     */
    public static $useBindings = true;

    /**
     * @var CustomObject
     */
    protected $baseObject;

    /**
     * @var FilterCriteria
     */
    protected $filterCriteria;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var Order[]
     */
    protected $orders;

    /**
     * @var GroupBy[]
     */
    protected $groupBys;

    /**
     * @var string
     */
    protected $statement = 'SELECT';

    /**
     * @var bool
     */
    protected $countOnly = false;

    /**
     * @var array
     */
    public $supportedStatements = ['SELECT', 'UPDATE'];

    /**
     * @var array
     */
    public $columnQueries = [];

    /**
     * @var array
     */
    public $joinQueries = [];

    /**
     * @var array
     */
    public $filterQueries = [];

    /**
     * @var array
     */
    public $joinConditionalQueries = [];

    /**
     * @var array
     */
    public $searchQueries = [];

    /**
     * @var array
     */
    public $orderQueries = [];

    /**
     * @var array
     */
    public $groupByQueries = [];

    /**
     * @var array
     */
    public $filterCriteriaParts = [];

    /**
     * @var string
     */
    public $filterCriteriaString = '';

    /**
     * @var array
     */
    public $filterCriteriaUids = [];

    /**
     * @var array
     */
    public $bindings = [];

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->groupBys = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return CustomObject
     */
    public function getBaseObject(): CustomObject
    {
        return $this->baseObject;
    }

    /**
     * @param CustomObject $baseObject
     */
    public function setBaseObject(CustomObject $baseObject): void
    {
        $this->baseObject = $baseObject;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch(string $search): void
    {
        $this->search = $search;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * @param string $statement
     */
    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    /**
     * @return bool
     */
    public function isCountOnly(): bool
    {
        return $this->countOnly;
    }

    /**
     * @param bool $countOnly
     */
    public function setCountOnly(bool $countOnly): void
    {
        $this->countOnly = $countOnly;
    }

    /**
     * @return FilterCriteria
     */
    public function getFilterCriteria(): FilterCriteria
    {
        return $this->filterCriteria;
    }

    /**
     * @param FilterCriteria $filterCriteria
     */
    public function setFilterCriteria(FilterCriteria $filterCriteria): void
    {
        $this->filterCriteria = $filterCriteria;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        $this->orders[] = $order;
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
        }

        return $this;
    }

    /**
     * @return Collection|GroupBy[]
     */
    public function getGroupBys(): Collection
    {
        return $this->groupBys;
    }

    public function addGroupBy(GroupBy $groupBy): self
    {
        $this->groupBys[] = $groupBy;
        return $this;
    }

    public function removeGroupBy(GroupBy $groupBy): self
    {
        if ($this->groupBys->contains($groupBy)) {
            $this->groupBys->removeElement($groupBy);
        }

        return $this;
    }

    /**
     * This function needs to be called to generate an alias for each Join and then
     * that alias needs to be added to each column and filter being applied to the query
     */
    public function generateAlias() {
        // MAKE SURE TO ONLY LETTERS HERE AND NOT NUMBERS AS NUMBERS
        // CAN CAUSE WEIRD MYSQL ISSUES WITH ALIASES
        $randomString = $this->generateRandomCharacters(5);
        $this->alias = sprintf("%s.%s", $randomString, $this->getBaseObject()->getInternalName());
        return $this->alias;
    }

    public function generateAliases() {

        // setup the base alias for the root object
        $alias = $this->generateAlias();
        foreach($this->getColumns() as $column) {
            $column->setAlias($alias);
        }
        foreach($this->getFilters() as $filter) {
            $filter->setAlias($alias);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateAliases($this->baseObject, $this);
        }

        return $this;
    }

    /**
     * Run validation of the query. This should be run prior to calling getQuery()
     * To check to make sure your query is formatted properly
     *
     * @return $this
     */
    public function validate() {

        // make sure each of the provided filter criteria have a matching filter
        if(!empty(array_diff($this->filterCriteria->getAllUids(), $this->getAllFilterUids()))) {
            throw new ApiProblemException(400, sprintf('Each filter criteria uid must match a provided filter uid. Filter Criteria UIDS that don\'t match are: %s',
            implode(",", array_diff($this->filterCriteria->getAllUids(), $this->getAllFilterUids()))));
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->validate();
        }

        /** @var Filter $filter */
        foreach($this->filters as $filter) {
            $filter->validate();
        }

        return $this;
    }

    public function generateColumnQueries() {

        foreach($this->getColumns() as $column) {

            if($this::$useBindings) {
                $column->getQueryWithBindings($this);
            } else {
                $column->getQuery($this);
            }
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($this);
        }

        return $this;
    }

    public function generateFilterQueries() {

        foreach($this->getFilters() as $filter) {

            if($this::$useBindings) {
                $filter->getQueryWithBindings($this);
            } else {
                $filter->getQuery($this);
            }
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($this);
        }

        return $this;
    }

    public function getAllFilterUids() {
        $uids = [];
        foreach($this->getFilters() as $filter) {
            $uids[] = $filter->getUid();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $uids = $join->getAllFilterUids($uids);
        }
        return $uids;
    }

    public function generateFilterCriteria() {

        if($this->filterCriteria) {
            $this->filterCriteria->generateFilterCriteria($this);
        }

        return $this;
    }

    public function generateSearchQueries() {

        if(empty($this->search)) {
            return $this;
        }

        foreach($this->getColumns() as $column) {
            $this->searchQueries[] = $column->getSearchQuery($this->search);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateSearchQueries($this);
        }

        return $this;
    }

    public function generateOrderQueries() {

        foreach($this->getOrders() as $order) {
            $priority = $this->determineKeyAvailability($this->orderQueries, $order->getPriority());

            if($column = $this->getColumnByUid($order->getUid())) {
                $this->orderQueries[$priority] = $order->getQuery($column);
            }
        }

        return $this;
    }

    public function generateGroupByQueries() {

        foreach($this->getGroupBys() as $groupBy) {

            if($column = $this->getColumnByUid($groupBy->getUid())) {
                $this->groupByQueries[] = $groupBy->getQuery($column);
            }
        }

        return $this;
    }

    public function generateJoinQueries() {

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinQueries($this);
        }

        return $this;
    }

    public function generateJoinConditionalQueries() {

        if($this::$useBindings) {
            $this->joinConditionalQueries[] = array(
                'sql' => sprintf("`%s`.custom_object_id  = ?", $this->getAlias()),
                'bindings' => [$this->baseObject->getId()]
            );
        } else {
            $this->joinConditionalQueries[] = array(
                'sql' => sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->baseObject->getId()),
                'bindings' => []
            );
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinConditionalQueries($this);
        }

        return $this;
    }

    public function getQuery() {

        $this->clearCache()
            ->validate()
            ->generateAliases()
            ->generateColumnQueries()
            ->generateFilterCriteria()
            ->generateFilterQueries()
            ->generateJoinQueries()
            ->generateJoinConditionalQueries()
            ->generateSearchQueries()
            ->generateOrderQueries()
            ->generateGroupByQueries();
        
        // We don't want to configure bindings for any of the column queries if we are just counting results
        if(!$this->countOnly) {
            foreach($this->columnQueries as $row) {
                $this->bindings = array_merge($this->bindings, $row['bindings']);
            }
        }

        $columnStr = implode(",\n",array_map(function($e) { return $e['sql']; }, $this->columnQueries));

        $joinString = implode("\n", array_map(function($e) { return $e['sql']; }, $this->joinQueries));
        foreach($this->joinQueries as $row) {
            $this->bindings = array_merge($this->bindings, $row['bindings']);
        }

        $joinConditionalString = !empty($this->joinConditionalQueries) ? sprintf("(\n%s\n)", implode(" AND \n", array_map(function($e) { return $e['sql']; }, $this->joinConditionalQueries))) : '';
        if (!empty($this->joinConditionalQueries)) {
            foreach($this->joinConditionalQueries as $row) {
                $this->bindings = array_merge($this->bindings, $row['bindings']);
            }
        }

        foreach($this->filterCriteriaParts as $i => $part) {
            if (array_key_exists($part, $this->filterQueries)) {
                $this->filterCriteriaParts[$i] = $this->filterQueries[$part]['sql'];
                $this->bindings = array_merge($this->bindings, $this->filterQueries[$part]['bindings']);
            }
        }

        $filterString = empty($this->filterCriteriaParts) ? '' : implode(" ", $this->filterCriteriaParts);

        $searchString = !empty($this->searchQueries) ? sprintf("(\n%s\n)", implode(" OR \n", $this->searchQueries)) : '';
        $searchString = empty($this->searchQueries) ? '' : "AND $searchString";

        /**
         * SET THE GROUP BY
         * This ensures that duplicate rows don't get returned with the same root object ID
         * https://stackoverflow.com/questions/23921117/disable-only-full-group-by/23921234
         */
        $groupString = sprintf(" \nGROUP BY `%s`.id\n", $this->getAlias());

        ksort($this->orderQueries);
        $orderString = !empty($this->orderQueries) ? sprintf("ORDER BY %s", implode(", \n", $this->orderQueries)) : '';

        $groupString = !empty($this->groupByQueries) ? sprintf(" \nGROUP BY %s\n", implode(", ", $this->groupByQueries)) : '';

        $limitString = $this->limit !== null ? sprintf("LIMIT %s \n", $this->limit) : '';
        $offsetString = $this->offset !== null ? sprintf("OFFSET %s \n", $this->offset) : '';

        if($this->statement === 'SELECT') {

            if ($this->countOnly) {
                $query = "SELECT COUNT(*) AS 'main_query_count'\n";
            } else {
                $query = sprintf("SELECT `%s`.id %s%s\n",
                    $this->getAlias(),
                    !empty($this->columnQueries) ?  ", \n" : '',
                    $columnStr
                );
            }

            $query = sprintf("%s from record `%s` %s WHERE \n %s \n %s \n %s \n %s %s %s %s",
                $query,
                $this->getAlias(),
                $joinString,
                $joinConditionalString,
                $filterString,
                $searchString,
                $groupString,
                $orderString,
                $limitString,
                $offsetString
            );
        } elseif ($this->statement === 'UPDATE') {
            $query = sprintf("UPDATE record `%s` %s SET %s WHERE \n %s \n %s \n %s",
                $this->getAlias(),
                $joinString,
                $columnStr,
                $joinConditionalString,
                $filterString,
                $searchString
            );
        } else {
            throw new ApiProblemException(400, sprintf('Statement %s not supported. Supported statements are: %s',
                $this->statement,
                implode(",", $this->supportedStatements)
            ));
        }

        return $query;
    }

    public function clearCache() {

        $this->columnQueries = [];
        $this->joinQueries = [];
        $this->filterQueries = [];
        $this->joinConditionalQueries = [];
        $this->searchQueries = [];
        $this->orderQueries = [];
        $this->groupByQueries = [];
        $this->filterCriteriaParts = [];
        $this->filterCriteriaString = '';
        $this->filterCriteriaUids = [];
        $this->bindings = [];

        return $this;
    }

    public function runQuery(EntityManagerInterface $entityManager) {

        $query = $this->getQuery();

        try {
            $stmt = $entityManager->getConnection()->prepare($query);
        } catch (\Exception $exception) {
            throw new ApiProblemException(400, sprintf('Error running query. Contact system administrator %s', $exception->getMessage()));
        }

        // todo possibly pass the type as an associative array with each binding Ex: $this->bindings['integer'] => $.'first_name', etc
        //  so you can make sure you are setting the correct type in the prepared statement
        $this->bindParameters($stmt);

        try {
            if(!$stmt->execute()) {
                throw new ApiProblemException(400, 'Error running query. Contact system administrator');
            }
        } catch (\Exception $exception) {
            throw new ApiProblemException(400, $exception->getMessage());
        }

        if($this->getStatement() === 'SELECT') {
            $results = $stmt->fetchAll();
            if ($this->countOnly) {
                return array(
                    'count' => count($results) ? $results[0]['main_query_count'] : 0,
                    "results"  => [],
                );
            } else {
                return array(
                    'count' => count($results),
                    "results"  => $results,
                );
            }
        } elseif ($this->getStatement() === 'UPDATE') {
            return array("results"  => 'Records successfully updated.');
        } else {
            throw new ApiProblemException(400, 'Statement not supported');
        }
    }

    private function bindParameters(\Doctrine\DBAL\Driver\Statement $stmt) {

        // binding must be passed by reference
        foreach($this->bindings as $index => &$binding) {
            if(is_int($binding)) {
                $stmt->bindParam($index + 1, $binding, ParameterType::INTEGER);
            } elseif(is_string($binding)) {
                $stmt->bindParam($index + 1, $binding, ParameterType::STRING);
            } elseif(is_bool($binding)) {
                $stmt->bindParam($index + 1, $binding, ParameterType::BOOLEAN);
            }
        }

    }
}