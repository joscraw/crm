<?php

namespace App\Model\Filter;

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
     * This function sets up the property fields we are querying
     * @return array
     */
    public function getQuery()
    {
        $internalName = $this->getProperty()->getInternalName();
        $label = $this->getProperty()->getLabel();
        $alias = $this->getAlias();
        $resultStr = '';

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
}