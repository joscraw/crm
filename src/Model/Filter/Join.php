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
            $column->getQuery($filterData);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($filterData);
        }
    }

    public function generateFilterQueries(FilterData $filterData) {

        /** @var Filter $filter */
        foreach($this->getFilters() as $filter) {
            $filter->getQuery($filterData);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($filterData);
        }
    }

    public function getAllFilterUids($uids = []) {

        /** @var Filter $filter */
        foreach($this->getFilters() as $filter) {
            $uids[] = $filter->getUid();
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            return $join->getAllFilterUids($uids);
        }

        return $uids;
    }

    public function generateSearchQueries(FilterData $filterData, AndCriteria $andCriteria) {

        foreach($this->getColumns() as $column) {
            $uid = $this->generateRandomNumber(5);
            $filter = new Filter();
            $filter->setProperty($column->getProperty());
            $filter->setAlias($column->getAlias());
            $filter->setOperator(Filter::CONTAINS);
            $filter->setValue($filterData->getSearch());
            $filter->setUid($uid);
            $this->addFilter($filter);
            $orCriteria = new OrCriteria();
            $orCriteria->setUid($uid);
            $andCriteria->addOrCriteria($orCriteria);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateSearchQueries($filterData, $andCriteria);
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
            $filterData->joinConditionalQueries[] = array(
                'sql' => sprintf("`%s`.custom_object_id  = ?", $this->getAlias()),
                'bindings' => [$this->joinObject->getId()],
            );
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

        /** @var Filter $filter */
        foreach($this->filters as $filter) {
            $filter->validate();
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
        if($joinType === 'With' && $joinDirection === 'normal_join') {
            $filterData->joinQueries[] = array(
                'sql' => sprintf($this->getJoinQuery(), $alias, $parentAlias, $alias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName())],
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'normal_join') {
            $filterData->joinQueries[] = array(
                'sql' => sprintf($this->getWithOrWithoutJoinQuery(), $alias, $parentAlias, $alias, $alias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName()), $this->joinObject->getId()],
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'normal_join') {
            // You actually aren't performing a join here but rather checking for null.
            $filterData->joinConditionalQueries[] = array(
                'sql' => sprintf($this->getWithoutJoinQuery(), $parentAlias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName())],
            );
        } elseif ($joinType === 'Without' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = array(
                'sql' => sprintf($this->getWithoutCrossJoinQuery(), $alias, $alias, $parentAlias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName())],
            );
        }
        elseif ($joinType === 'With' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = array(
                'sql' => sprintf($this->getCrossJoinQuery(), $alias, $alias, $parentAlias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName())],
            );
        } elseif ($joinType === 'With/Without' && $joinDirection === 'cross_join') {
            $filterData->joinQueries[] = array(
                'sql' => sprintf($this->getWithOrWithoutCrossJoinQuery(), $alias, $alias, $parentAlias, $alias),
                'bindings' => [sprintf('$."%s"', $connectedProperty->getInternalName()), $this->joinObject->getId()],
            );
        }
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
INNER JOIN record `%s` on `%s`.properties->>? REGEXP concat('(^|;)', `%s`.id, '(;|$)') 
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
LEFT JOIN record `%s` on 
     (`%s`.properties->>? REGEXP concat('(^|;)', `%s`.id, '(;|$)') AND `%s`.custom_object_id = ?)
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutJoinQuery() {
        return <<<HERE
(COALESCE(`%s`.properties->>?, '') = '')
HERE;
    }

    /**
     * Normal Join Looking for records without a match
     * @return string
     */
    private function getWithoutCrossJoinQuery() {
        return <<<HERE
LEFT JOIN record `%s` on `%s`.properties->>? NOT REGEXP concat('(^|;)', `%s`.id, '(;|$)')
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
INNER JOIN record `%s` on `%s`.properties->>? REGEXP concat('(^|;)', `%s`.id, '(;|$)')
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
LEFT JOIN record `%s` on 
     (`%s`.properties->>? REGEXP concat('(^|;)', `%s`.id, '(;|$)') AND `%s`.custom_object_id = ?)
HERE;
    }
}