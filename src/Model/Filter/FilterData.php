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
     * This function needs to be called to generate an alias for each Join and then
     * that alias needs to be added to each column and filter being applied to the query
     */
    public function generateAlias() {
        $randomString = $this->generateRandomString(5);
        $this->alias = sprintf("%s.%s", $randomString, $this->getBaseObject()->getInternalName());
        return $this->alias;
    }


    // TODO REFACTORED CODE

    public function generateAliases() {

        // setup the base alias for the root object
        $alias = $this->generateAlias();
        foreach($this->getColumns() as $column) {
            $column->setAlias($alias);
        }
        foreach($this->getOrFilters() as $orFilter) {
            $orFilter->setAlias($alias);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateAliases($this->baseObject, $this);
        }
    }

    public function generateColumnQueries() {

        foreach($this->getColumns() as $column) {
            $this->columnQueries[] = $this->columnQuery($column);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateColumnQueries($this);
        }
    }

    public function generateFilterQueries() {

        foreach($this->getOrFilters() as $orFilter) {
            $this->filterQueries[] = $this->filterQuery($orFilter);
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateFilterQueries($this);
        }
    }

    public function generateJoinQueries() {

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinQueries($this);
        }
    }

    public function generateJoinConditionalQueries() {

        $this->joinConditionalQueries[] = sprintf("`%s`.custom_object_id = %s", $this->getAlias(), $this->baseObject->getId());

        /** @var Join $join */
        foreach($this->joins as $join) {
            $join->generateJoinConditionalQueries($this);
        }
    }

    // TODO FINISHED REFACTORED CODE



    public function joinConditionalQuery(Join $join) {

        // HANDLE CROSS JOINS AND NORMAL JOINS

        if($join->getRelationshipPropertyToJoinOn()->getCustomObject()->getId() === $previousObject->getId()) {
            return sprintf("`%s`.custom_object_id = %s", $join->getAlias(), $join->getRelationshipPropertyToJoinOn()->getField()->getCustomObject()->getId());
        }

        return sprintf("`%s`.custom_object_id = %s", $join->getAlias(), $previousObject->getId());
    }

    public function joinQuery(Join $join) {

        $connectedProperty = $join->getRelationshipPropertyToJoinOn();
        $joinDirection = $join->getJoinDirection();
        $joinType = $join->getJoinType();
        $alias = $join->getAlias();
        $parentAlias = $join->getParentAlias();
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
     * This function sets up the property fields we are querying
     * @param Column $column
     * @return array
     */
    public function columnQuery(Column $column)
    {
        $internalName = $column->getProperty()->getInternalName();
        $label = $column->getProperty()->getLabel();
        $alias = $column->getAlias();
        $resultStr = '';

        switch($column->getProperty()->getFieldType()) {
            case FieldCatalog::DATE_PICKER:
                $jsonExtract = $this->getDatePickerQuery($alias);
                $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $jsonExtract = $this->getSingleCheckboxQuery($alias);
                $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $internalName, $internalName, $label);
                break;
            case FieldCatalog::NUMBER:
                if($column->getProperty()->getField()->getType() === NumberField::$types['Currency']) {
                    $jsonExtract = $this->getNumberIsCurrencyQuery($alias);
                    $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                } elseif($column->getProperty()->getField()->getType() === NumberField::$types['Unformatted Number']) {
                    $jsonExtract = $this->getNumberIsUnformattedQuery($column->getAlias());
                    $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                }
                break;
            default:
                $jsonExtract = $this->getDefaultQuery($alias);
                $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                break;

        }
        return $resultStr;
    }

    /**
     * @param Filter $filter
     * @param $alias
     * @param null $data
     * @param bool $isChildFilter
     * @return string
     */
    public function filterQuery(Filter $filter, $alias = null, $data = null, $isChildFilter = false) {

        $query = '';
        $andFilters = [];
        switch($filter->getProperty()->getFieldType()) {
            case 'number_field':
                switch($filter->getOperator()) {
                    case 'EQ':
                        $value = str_replace(',', '', $filter->getValue());
                        if(trim($filter->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'%s\'', $filter->getAlias(), $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'NEQ':
                        $value = str_replace(',', '', $customFilter['value']);
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value);
                        }

                        break;
                    case 'LT':
                        $value = str_replace(',', '', $customFilter['value']);
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', null) < \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }

                        break;
                    case 'GT':
                        $value = str_replace(',', '', $customFilter['value']);
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $value, $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        }
                        break;
                    case 'BETWEEN':
                        $lowValue = str_replace(',', '', $customFilter['low_value']);
                        $highValue = str_replace(',', '', $customFilter['high_value']);
                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], $lowValue, $highValue);
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($filter->getOperator()) {
                    case 'EQ':
                        if(trim($filter->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName(), strtolower($filter->getValue()));
                        }
                        break;
                    case 'NEQ':
                        if(trim($filter->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName(), strtolower($filter->getValue()));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $filter->getAlias(), $filter->getProperty()->getInternalName(), $filter->getAlias(), $filter->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'date_picker_field':
                switch($customFilter['operator']) {
                    case 'EQ':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') = STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'NEQ':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') != STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'LT':
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') < \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') < STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'GT':
                        if(trim($customFilter['value']) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') > \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') > STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['value']);
                        }
                        break;
                    case 'BETWEEN':
                        if(trim($customFilter['low_value']) === '' || trim($customFilter['high_value']) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '', '');
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') BETWEEN STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\') AND STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $alias, $customFilter['internalName'], $customFilter['low_value'], $customFilter['high_value']);
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($customFilter['operator']) {
                    case 'IN':

                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\')', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1');
                            }
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\')', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], '1');
                            }
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                switch($customFilter['operator']) {
                    case 'IN':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)",implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
            case 'multiple_checkbox_field':
                switch($customFilter['operator']) {
                    case 'IN':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($customFilter['value']) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        } else {
                            $values = explode(',', $customFilter['value']);
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName'], strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $alias, $customFilter['internalName'], $alias, $customFilter['internalName']);
                        break;
                }
                break;
        }

        // add the child filters (AND conditionals)
    /*    if(!empty($customFilter['childFilters'])) {
            foreach($customFilter['childFilters'] as $uid => $childFilter) {
                $alias = !empty($data['filters'][$uid]['alias']) ? $data['filters'][$uid]['alias'] : $alias;
                $andFilters[] = $this->getConditionForReport($childFilter, $alias, $data, true);
            }
        }
        $query .= !empty($andFilters) ? implode(" AND ", $andFilters) : '';
        if(!$isChildFilter) {
            $query = sprintf("(\n%s\n)", $query) . PHP_EOL;
        }*/

        if(count($andFilters) > 0) {
            $query .= !empty($andFilters) ? implode(" AND ", $andFilters) : '';
            return $query;
        }
    }

    public function getQuery() {
        $columnStr = implode(",",$this->columnQueries);
        $columnStr  = !empty($columnStr) ? ', ' . $columnStr : '';

        $joinString = implode(" ", $this->joinQueries);

        $joinConditionalString = !empty($this->joinConditionalQueries) ? sprintf("(\n%s\n)", implode(" AND \n", $this->joinConditionalQueries)) : '';

        $filterString = !empty($this->filterQueries) ? sprintf("(\n%s)", implode(" OR \n", $this->filterQueries)) : '';
        $filterString = empty($this->filterQueries) ? '' : "AND $filterString";

        $query = sprintf("SELECT DISTINCT `%s`.id %s from record `%s` %s WHERE \n %s \n %s", $this->getAlias(), $columnStr, $this->getAlias(), $joinString, $joinConditionalString, $filterString);

        return $query;
    }

    private function getDatePickerQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getNumberIsCurrencyQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE CAST( `${alias}`.properties->>'$."%s"' AS DECIMAL(15,2) ) 
    END AS "%s"
HERE;
    }

    private function getNumberIsUnformattedQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getDefaultQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "-" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getSingleCheckboxQuery($alias = 'r1') {
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