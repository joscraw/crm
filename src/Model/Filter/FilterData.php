<?php

namespace App\Model\Filter;

use App\Api\ApiProblemException;
use App\Entity\CustomObject;
use App\Entity\Property;
use App\Model\FieldCatalog;
use App\Model\NumberField;
use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class FilterData extends AbstractFilter
{
    use RandomStringGenerator;

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
    public $filterCriteriaParts = [];

    /**
     * @var string
     */
    public $filterCriteriaString = '';

    /**
     * @var array
     */
    public $filterUids = [];

    /**
     * @var array
     */
    public $filterCriteriaUids = [];

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
        foreach($this->getOrders() as $order) {
            $order->setAlias($alias);
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
        if(!empty(array_diff($this->filterCriteriaUids, $this->filterUids))) {
            throw new ApiProblemException(400, sprintf('Each filter criteria uid must match a provided filter uid. Filter Criteria UIDS that don\'t match are: %s',
            implode(",", array_diff($this->filterCriteriaUids, $this->filterUids))));
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->validate();
        }
        return $this;
    }

    public function generateColumnQueries() {

        foreach($this->getColumns() as $column) {
            $this->columnQueries[] = $column->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($this);
        }

        return $this;
    }

    public function generateFilterQueries() {

        foreach($this->getFilters() as $filter) {
            $this->filterQueries[] = $filter->getQuery($this);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($this);
        }

        return $this;
    }

    public function generateFilterCriteria() {

        $this->filterCriteria->generateFilterCriteria($this);

        $this->filterCriteriaString = implode(" ", $this->filterCriteriaParts);

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
            $this->orderQueries[$order->getPriority()] = $order->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateOrderQueries($this);
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

        $this->joinConditionalQueries[] = sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->baseObject->getId());

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinConditionalQueries($this);
        }

        return $this;
    }

    public function getQuery() {
        $columnStr = implode(",",$this->columnQueries);
        $columnStr  = !empty($columnStr) ? ', ' . $columnStr : '';

        $joinString = implode(" ", $this->joinQueries);

        $joinConditionalString = !empty($this->joinConditionalQueries) ? sprintf("(\n%s\n)", implode(" AND \n", $this->joinConditionalQueries)) : '';

        $filterString = empty($this->filterCriteriaParts) || empty($this->filterCriteriaString) ? '' : "AND {$this->filterCriteriaString}";

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

        $limitString = $this->limit !== null ? sprintf("LIMIT %s \n", $this->limit) : '';
        $offsetString = $this->offset !== null ? sprintf("OFFSET %s \n", $this->offset) : '';

        $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE \n %s \n %s \n %s \n %s %s %s %s",
            $this->getAlias(),
            $columnStr,
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

        return $query;
    }
}