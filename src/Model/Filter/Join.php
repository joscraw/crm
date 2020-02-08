<?php

namespace App\Model\Filter;

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

        $randomString = $this->generateRandomString(5);
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
            // If the property we are joining on does not live on a different object
            $this->joinDirection = 'cross_join';
            $customObject = $this->getRelationshipPropertyToJoinOn()->getCustomObject();
            $alias = $this->generateAlias($customObject);
        }

        $this->joinObject = $customObject;

        foreach($this->getColumns() as $column) {
            $column->setAlias($alias);
        }

        foreach($this->getOrFilters() as $orFilter) {
            $orFilter->setAlias($alias);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateAliases($customObject, $join);
        }
    }

    public function generateColumnQueries(FilterData $filterData) {

        /** @var Column $column */
        foreach($this->getColumns() as $column) {
            $filterData->columnQueries[] = $column->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($filterData);
        }
    }

    public function generateFilterQueries(FilterData $filterData) {

        /** @var Filter $orFilter */
        foreach($this->getOrFilters() as $orFilter) {
            $filterData->filterQueries[] = $orFilter->getQuery();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($filterData);
        }
    }

    public function generateJoinQueries(FilterData $filterData) {

        $filterData->joinQueries[] = $this->getQuery();

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinQueries($filterData);
        }
    }

    public function generateJoinConditionalQueries(FilterData $filterData) {

        $filterData->joinConditionalQueries[] = sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->joinObject->getId());
    }

    private function getQuery() {

        $connectedProperty = $this->getRelationshipPropertyToJoinOn();
        $joinDirection = $this->getJoinDirection();
        $joinType = $this->getJoinType();
        $alias = $this->getAlias();
        $parentAlias = $this->getParentAlias();
        $query = '';
        if($joinType === 'With' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getJoinQuery(),
                'INNER JOIN', $alias, $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getJoinQuery(),
                'LEFT JOIN', $alias, $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias,
                $parentAlias, $connectedProperty->getInternalName(), $alias
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
            $query = sprintf($this->getWithoutJoinQuery(), $parentAlias, $connectedProperty->getInternalName(), $parentAlias, $connectedProperty->getInternalName());
        } elseif ($joinType === 'With' && $joinDirection === 'cross_join') {
            $query = sprintf($this->getCrossJoinQuery(),
                'INNER JOIN', $alias, $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'cross_join') {
            $query = sprintf($this->getCrossJoinQuery(),
                'LEFT JOIN', $alias, $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'cross_join') {
            $query = sprintf($this->getWithoutCrossJoinQuery(),
                $alias, $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $parentAlias,
                $alias, $connectedProperty->getInternalName(), $alias, $connectedProperty->getInternalName());
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
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutJoinQuery() {
        return <<<HERE
    WHERE (`%s`.properties->>'$."%s"' IS NULL OR `%s`.properties->>'$."%s"' = '')
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutCrossJoinQuery() {
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
}