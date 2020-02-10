<?php

namespace App\Model\Filter;

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
     * This function needs to be called to generate an alias for each Join and then
     * that alias needs to be added to each column and filter being applied to the query
     */
    public function generateAlias() {
        $randomString = $this->generateRandomString(5);
        $this->alias = sprintf("%s.%s", $randomString, $this->getBaseObject()->getInternalName());
        return $this->alias;
    }

    public function generateAliases() {

        // setup the base alias for the root object
        $alias = $this->generateAlias();
        foreach($this->getColumns() as $column) {
            $column->setAlias($alias);
        }
        foreach($this->getOrFilters() as $orFilter) {
            $orFilter->setAlias($alias);
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

        foreach($this->getOrFilters() as $orFilter) {
            $this->filterQueries[] = $orFilter->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($this);
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

        $filterString = !empty($this->filterQueries) ? sprintf("(\n%s\n)", implode(" OR \n", $this->filterQueries)) : '';
        $filterString = empty($this->filterQueries) ? '' : "AND $filterString";

        $searchString = !empty($this->searchQueries) ? sprintf("(\n%s\n)", implode(" OR \n", $this->searchQueries)) : '';
        $searchString = empty($this->searchQueries) ? '' : "AND $searchString";

        ksort($this->orderQueries);
        $orderString = !empty($this->orderQueries) ? sprintf("ORDER BY %s", implode(", \n", $this->orderQueries)) : '';

        $limitString = $this->limit !== null ? sprintf("LIMIT %s \n", $this->limit) : '';
        $offsetString = $this->offset !== null ? sprintf("OFFSET %s \n", $this->offset) : '';

        $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE \n %s \n %s \n %s %s %s %s",
            $this->getAlias(),
            $columnStr,
            $this->getAlias(),
            $joinString,
            $joinConditionalString,
            $filterString,
            $searchString,
            $orderString,
            $limitString,
            $offsetString
        );

        return $query;
    }
}