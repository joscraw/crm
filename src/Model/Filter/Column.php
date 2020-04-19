<?php

namespace App\Model\Filter;

use App\Entity\Property;
use App\Model\FieldCatalog;
use App\Model\NumberField;

class Column
{
    use Uid;

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
     * Gets query for column
     * @param FilterData $filterData
     */
    public function getQuery(FilterData $filterData)
    {
        $internalName = sprintf('$."%s"', $this->getProperty()->getInternalName());
        $label = !empty($this->renameTo) ? $this->renameTo : $this->getProperty()->getLabel();
        $alias = $this->getAlias();
        $result = [];

       if($filterData->getStatement() === 'UPDATE') {
           $result['sql'] = $this->getUpdateQuery($alias);
           $result['bindings'] = [$internalName, $this->newValue];
           $filterData->columnQueries[] = $result;
           return;
       }

        switch($this->getProperty()->getFieldType()) {
            case FieldCatalog::DATE_PICKER:
                $result['sql'] = $this->getDatePickerQuery($alias);
                $result['bindings'] = [$internalName, $label];
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $result['sql'] = $this->getSingleCheckboxQuery($alias);
                $result['bindings'] = [$internalName, $internalName, $internalName, $internalName, $internalName, $label];
                break;
            case FieldCatalog::NUMBER:
                if($this->getProperty()->getField()->getType() === NumberField::$types['Currency']) {
                    $result['sql'] = $this->getNumberIsCurrencyQuery($alias);
                    $result['bindings'] = [$internalName, $label];
                } elseif($this->getProperty()->getField()->getType() === NumberField::$types['Unformatted Number']) {
                    $result['sql']= $this->getNumberIsUnformattedQuery($alias);
                    $result['bindings'] = [$internalName, $label];
                }
                break;
            default:
                $result['sql'] = $this->getDefaultQuery($alias);
                $result['bindings'] = [$internalName, $label];
                break;

        }

        $filterData->columnQueries[] = $result;
    }

    private function getUpdateQuery($alias) {
        return <<<HERE
    `${alias}`.properties = JSON_SET(`${alias}`.properties, ?, ?) \n
HERE;
    }

    private function getDatePickerQuery($alias) {
        return <<<HERE
COALESCE(`${alias}`.properties->>?, "") AS ?
HERE;
    }

    // TODO REVISIT THIS. I'M STARTING TO THINK THAT THE FORMATTING SHOULD BE ON THE SAVE SIDE AND NOT
    //  THE ACTUAL QUERYING SIDE OF THIS.
    private function getNumberIsCurrencyQuery($alias) {
        return <<<HERE
    COALESCE( CAST( `${alias}`.properties->>? AS DECIMAL(15,2) ), "") AS ?
HERE;
    }

    private function getNumberIsUnformattedQuery($alias) {
        return <<<HERE
    COALESCE(`${alias}`.properties->>?, "") AS ?
HERE;
    }

    private function getDefaultQuery($alias) {
        return <<<HERE
    COALESCE(`${alias}`.properties->>?, "") AS ?
HERE;
    }

    private function getSingleCheckboxQuery($alias) {
        return <<<HERE
    CASE
        WHEN `${alias}`.properties->>? IS NULL THEN "" 
        WHEN `${alias}`.properties->>? = '' THEN ""
        WHEN `${alias}`.properties->>? = '1' THEN "yes"
        WHEN `${alias}`.properties->>? = '0' THEN "no"
        ELSE `${alias}`.properties->>?
    END AS ?
HERE;
    }
}