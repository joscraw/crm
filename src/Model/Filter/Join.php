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

        if($previousObject->getId() === $this->getRelationshipPropertyToJoinOn()->getCustomObject()->getId()) {
            // the property we are joining on actually lives on the previous object
            $this->joinDirection = 'normal_join';
            $customObject = $this->getRelationshipPropertyToJoinOn()->getField()->getCustomObject();
            $alias = $this->generateAlias($customObject);
        } else {
            // the property we are joining on does not live on the previous object but rather on another object
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

        foreach($this->getColumns() as $column) {
            $filterData->columnQueries[] = $filterData->columnQuery($column);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($filterData);
        }
    }

    public function generateFilterQueries(FilterData $filterData) {

        foreach($this->getOrFilters() as $orFilter) {
            $filterData->filterQueries[] = $filterData->filterQuery($orFilter);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($filterData);
        }
    }

    public function generateJoinQueries(FilterData $filterData) {

        $filterData->joinQueries[] = $filterData->joinQuery($this);

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinQueries($filterData);
        }
    }

    public function generateJoinConditionalQueries(FilterData $filterData) {

        $filterData->joinConditionalQueries[] = sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->joinObject->getId());
    }
}