<?php

namespace App\Model\Filter;

use App\Api\ApiProblemException;
use App\Entity\Property;
use App\Model\FieldCatalog;
use App\Model\NumberField;

class Column
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $renameTo;

    /**
     * @var string
     */
    protected $newValue;

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property): void
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getRenameTo(): string
    {
        return $this->renameTo;
    }

    /**
     * @param string $renameTo
     */
    public function setRenameTo(string $renameTo): void
    {
        $this->renameTo = $renameTo;
    }

    /**
     * @return string
     */
    public function getNewValue(): string
    {
        return $this->newValue;
    }

    /**
     * @param string $newValue
     */
    public function setNewValue(string $newValue): void
    {
        $this->newValue = $newValue;
    }

    /**
     * This function sets up the property fields we are querying
     * @param FilterData $filterData
     * @return array
     */
    public function getQuery(FilterData $filterData)
    {
        $internalName = $this->getProperty()->getInternalName();
        $label = !empty($this->renameTo) ? $this->renameTo : $this->getProperty()->getLabel();
        $alias = $this->getAlias();
        $resultStr = '';

        if($filterData->getStatement() === 'UPDATE') {
            $jsonExtract = $this->getUpdateQuery($alias);
            $resultStr = sprintf($jsonExtract, $internalName, $this->newValue);
            return $resultStr;
        }

        switch($this->getProperty()->getFieldType()) {
            case FieldCatalog::DATE_PICKER:
                $jsonExtract = $this->getDatePickerQuery($alias);
                $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $jsonExtract = $this->getSingleCheckboxQuery($alias);
                $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $internalName, $internalName, $label);
                break;
            case FieldCatalog::NUMBER:
                if($this->getProperty()->getField()->getType() === NumberField::$types['Currency']) {
                    $jsonExtract = $this->getNumberIsCurrencyQuery($alias);
                    $resultStr = sprintf($jsonExtract, $internalName, $internalName, $internalName, $label);
                } elseif($this->getProperty()->getField()->getType() === NumberField::$types['Unformatted Number']) {
                    $jsonExtract = $this->getNumberIsUnformattedQuery($alias);
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

    public function getSearchQuery($search) {

        $searchQuery = <<<HERE
    LOWER(`%s`.properties->>'$."%s"') LIKE '%%%s%%'
HERE;
        return sprintf($searchQuery, $this->alias, $this->getProperty()->getInternalName(), strtolower($search));
    }

    private function getDatePickerQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getNumberIsCurrencyQuery($alias = 'r1') {
        return <<<HERE
    CASE 
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE CAST( `${alias}`.properties->>'$."%s"' AS DECIMAL(15,2) ) 
    END AS "%s"
HERE;
    }

    private function getNumberIsUnformattedQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getDefaultQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getSingleCheckboxQuery($alias = 'r1') {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>'$."%s"' IS NULL THEN "" 
        WHEN `${alias}`.properties->>'$."%s"' = '' THEN ""
        WHEN `${alias}`.properties->>'$."%s"' = '1' THEN "yes"
        WHEN `${alias}`.properties->>'$."%s"' = '0' THEN "no"
        ELSE `${alias}`.properties->>'$."%s"'
    END AS "%s"
HERE;
    }

    private function getUpdateQuery($alias = 'r1') {
        return <<<HERE
    `${alias}`.properties = JSON_SET(`${alias}`.properties, '$."%s"', "%s") \n
HERE;
    }

    //SET `%s`.properties = JSON_SET(`%s`.properties, '$.%s', '%s') \n
}