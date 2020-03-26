<?php

namespace App\Model\Filter;

use App\Api\ApiProblemException;
use App\Entity\CustomObject;
use App\Entity\Property;
use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Join extends AbstractFilter
{
    use RandomStringGenerator;

    /**
     * @var Property
     */
    protected $relationshipPropertyToJoinOn;

    /**
     * With/Without,etc
     * @var string
     */
    protected $joinExclusion;

    /**
     * Inner Join, Left Join, etc
     * @var string
     */
    protected $joinDirection;

    /**
     * normal_join/cross_join
     * @var string
     */
    protected $joinType;

    /**
     * @var string
     */
    protected $parentAlias;

    /**
     * @var AbstractFilter
     */
    protected $parent;

    /**
     * @var CustomObject
     */
    protected $joinObject;


    /**
     * @return string
     */
    public function getJoinType(): string
    {
        return $this->joinType;
    }

    /**
     * @param string $joinType
     */
    public function setJoinType(string $joinType): void
    {
        $this->joinType = $joinType;
    }

    /**
     * @return string
     */
    public function getJoinDirection(): string
    {
        return $this->joinDirection;
    }

    /**
     * @param string $joinDirection
     */
    public function setJoinDirection(string $joinDirection): void
    {
        $this->joinDirection = $joinDirection;
    }

    /**
     * @return Property
     */
    public function getRelationshipPropertyToJoinOn(): Property
    {
        return $this->relationshipPropertyToJoinOn;
    }

    /**
     * @param Property $relationshipPropertyToJoinOn
     */
    public function setRelationshipPropertyToJoinOn(Property $relationshipPropertyToJoinOn): void
    {
        $this->relationshipPropertyToJoinOn = $relationshipPropertyToJoinOn;
    }


    /**
     * @return string
     */
    public function getJoinExclusion(): string
    {
        return $this->joinExclusion;
    }

    /**
     * @param string $joinExclusion
     */
    public function setJoinExclusion(string $joinExclusion): void
    {
        $this->joinExclusion = $joinExclusion;
    }

    /**
     * @return string
     */
    public function getParentAlias(): string
    {
        return $this->parentAlias;
    }

    /**
     * @param string $parentAlias
     */
    public function setParentAlias(string $parentAlias): void
    {
        $this->parentAlias = $parentAlias;
    }

    /**
     * @return AbstractFilter
     */
    public function getParent(): AbstractFilter
    {
        return $this->parent;
    }

    /**
     * @param AbstractFilter $parent
     */
    public function setParent(AbstractFilter $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return CustomObject
     */
    public function getJoinObject(): CustomObject
    {
        return $this->joinObject;
    }

    /**
     * @param CustomObject $joinObject
     */
    public function setJoinObject(CustomObject $joinObject): void
    {
        $this->joinObject = $joinObject;
    }

    /**
     * This function needs to be called to generate an alias for each Join and then
     * that alias needs to be added to each column and filter being applied to the query
     * @param CustomObject $previousObject
     * @return string
     */
    public function generateAlias(CustomObject $previousObject) {

        // MAKE SURE TO ONLY LETTERS HERE AND NOT NUMBERS AS NUMBERS
        // CAN CAUSE WEIRD MYSQL ISSUES WITH ALIASES
        $randomString = $this->generateRandomCharacters(5);
        $this->alias = sprintf("%s.%s", $randomString, $previousObject->getInternalName());

        return $this->alias;
    }

    /**
     * @param CustomObject $previousObject
     * @param AbstractFilter $filterObject
     */
    public function generateAliases(CustomObject $previousObject, AbstractFilter $filterObject) {

        $this->parentAlias = $filterObject->getAlias();
        $this->parent = $filterObject;

        /**
         * The logic here should never really be touched. This determines whether or not we will
         * be performing a cross join or a normal join. See JoinQueries below for more details
         */
        if($previousObject->getId() === $this->getRelationshipPropertyToJoinOn()->getCustomObject()->getId()) {
            // If the property we are joining on actually lives on the same object
            $this->joinDirection = 'normal_join';
            $customObject = $this->getRelationshipPropertyToJoinOn()->getField()->getCustomObject();
            $alias = $this->generateAlias($customObject);
        } else {
            // If the property we are joining on lives on a different object
            $this->joinDirection = 'cross_join';
            $customObject = $this->getRelationshipPropertyToJoinOn()->getCustomObject();
            $alias = $this->generateAlias($customObject);
        }

        $this->joinObject = $customObject;

        foreach($this->getColumns() as $column) {
            $column->setAlias($alias);
        }

        foreach($this->getFilters() as $filter) {
            $filter->setAlias($alias);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateAliases($customObject, $this);
        }
    }

    public function generateColumnQueries(FilterData $filterData) {

        /** @var Column $column */
        foreach($this->getColumns() as $column) {
            $filterData->columnQueries[] = $column->getQuery($filterData);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($filterData);
        }
    }

    public function generateFilterQueries(FilterData $filterData) {

        /** @var Filter $filter */
        foreach($this->getFilters() as $filter) {
            $filterData->filterQueries[] = $filter->getQuery($filterData);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($filterData);
        }
    }

    public function generateSearchQueries(FilterData $filterData) {

        foreach($this->getColumns() as $column) {
            $filterData->searchQueries[] = $column->getSearchQuery($filterData->getSearch());
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateSearchQueries($filterData);
        }
    }

    public function generateOrderQueries(FilterData $filterData) {

        foreach($this->getOrders() as $order) {
            $priority = $this->determineKeyAvailability($filterData->orderQueries, $order->getPriority());
            $filterData->orderQueries[$priority] = $order->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateOrderQueries($filterData);
        }
    }

    public function generateJoinQueries(FilterData $filterData) {

        $this->getQuery($filterData);

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinQueries($filterData);
        }
    }

    public function generateJoinConditionalQueries(FilterData $filterData) {

        $skipJoinCondition = ($this->joinType === 'Without' && $this->joinDirection === 'normal_join') ||
            ($this->joinType === 'With/Without');

        if(!$skipJoinCondition) {
            $filterData->joinConditionalQueries[] = sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->joinObject->getId());
        }

        foreach($this->joins as $join) {
            $join->generateJoinConditionalQueries($filterData);
        }
    }

    public function hasColumns() {
        return $this->columns->count() > 0;
    }

    public function hasFilters() {
        return $this->filters->count() > 0;
    }

    public function validate() {

        if($this->joinType === 'Without' && ($this->hasColumns() || $this->hasFilters())) {
            throw new ApiProblemException(400,
                sprintf('"Without" joinTypes cannot have "columns" or "filters" as you are 
                requesting all records that do NOT have a relationship with property %s (%s). 
                Please set both "columns" and "filters" to any empty array [].',
                    $this->relationshipPropertyToJoinOn->getId(),
                    $this->relationshipPropertyToJoinOn->getInternalName()
                ));
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->validate();
        }

    }

    private function getQuery(FilterData $filterData) {

        $connectedProperty = $this->getRelationshipPropertyToJoinOn();
        $joinDirection = $this->getJoinDirection();
        $joinType = $this->getJoinType();
        $alias = $this->getAlias();
        $parentAlias = $this->getParentAlias();
        $query = '';
        if($joinType === 'With' && $joinDirection === 'normal_join') {
            $filterData->joinQueries[] = sprintf($this->getJoinQuery(),
                'INNER JOIN', $alias, $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'normal_join') {
            $filterData->joinQueries[] = sprintf($this->getWithOrWithoutJoinQuery(),
                $alias, $parentAlias, $connectedProperty->getInternalName(), $alias, $alias, $this->joinObject->getId(),
                $parentAlias, $connectedProperty->getInternalName(), $alias, $alias, $this->joinObject->getId(),
                $parentAlias, $connectedProperty->getInternalName(), $alias, $alias, $this->joinObject->getId(),
                $parentAlias, $connectedProperty->getInternalName(), $alias, $alias, $this->joinObject->getId()
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
            // You actually aren't performing a join here but rather checking for null.
            $filterData->joinConditionalQueries[] = sprintf($this->getWithoutJoinQuery(), $parentAlias, $connectedProperty->getInternalName(), $parentAlias, $connectedProperty->getInternalName());
        } elseif ($joinType === 'Without' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = sprintf($this->getWithoutCrossJoinQuery(),
                $alias, $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $alias, $connectedProperty->getInternalName());
        }
        elseif ($joinType === 'With' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = sprintf($this->getCrossJoinQuery(),
                'INNER JOIN', $alias, $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = sprintf($this->getWithOrWithoutCrossJoinQuery(),
                $alias, $alias, $connectedProperty->getInternalName(), $parentAlias, $alias, $this->joinObject->getId(),
                $alias, $connectedProperty->getInternalName(), $parentAlias, $alias, $this->joinObject->getId(),
                $alias, $connectedProperty->getInternalName(), $parentAlias, $alias, $this->joinObject->getId(),
                $alias, $connectedProperty->getInternalName(), $parentAlias, $alias, $this->joinObject->getId()
            );
        }
        return $query;
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
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     * @return string
     */
    private function getWithOrWithoutJoinQuery() {
        return <<<HERE

    /* Given the id "11" This first statement matches: {"property_name": "11"} */
     LEFT JOIN record `%s` on 
     (`%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, '$') AND `%s`.custom_object_id = '%s')
     /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, '$') AND `%s`.custom_object_id = '%s')
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, ';') AND `%s`.custom_object_id = '%s')
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, ';')AND `%s`.custom_object_id = '%s')

HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutJoinQuery() {
        return <<<HERE
     (`%s`.properties->>'$."%s"' IS NULL OR `%s`.properties->>'$."%s"' = '')
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutCrossJoinQuery() {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
    LEFT JOIN record `%s` on `%s`.properties->>'$."%s"' NOT REGEXP concat('^', `%s`.id, '$')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     AND `%s`.properties->>'$."%s"' NOT REGEXP concat(';', `%s`.id, '$') 
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     AND `%s`.properties->>'$."%s"' NOT REGEXP concat(';', `%s`.id, ';') 
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     AND `%s`.properties->>'$."%s"' NOT REGEXP concat('^', `%s`.id, ';')
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
     * We store relations to a single object as a string.
     * We store relations to multiple objects as a semicolon delimited string
     * Single object example: {chapter: "11"}
     * Multiple object example: {chapter: "11;12;13"}
     * @return string
     */
    private function getWithOrWithoutCrossJoinQuery() {
        return <<<HERE
    /* Given the id "11" This first statement matches: {"property_name": "11"} */
     LEFT JOIN record `%s` on 
     (`%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, '$') AND `%s`.custom_object_id = '%s')
    /* Given the id "11" This second statement matches: {"property_name": "12;11"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, '$') AND `%s`.custom_object_id = '%s')
     /* Given the id "11" This second statement matches: {"property_name": "12;11;13"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat(';', `%s`.id, ';') AND `%s`.custom_object_id = '%s')
     /* Given the id "11" This second statement matches: {"property_name": "11;12;13"} */
     OR (`%s`.properties->>'$."%s"' REGEXP concat('^', `%s`.id, ';') AND `%s`.custom_object_id = '%s')
HERE;
    }
}