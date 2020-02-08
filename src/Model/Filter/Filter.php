<?php

namespace App\Model\Filter;

use App\Entity\Property;

class Filter
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $lowValue;

    /**
     * @var string
     */
    protected $highValue;

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
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
    public function getLowValue(): string
    {
        return $this->lowValue;
    }

    /**
     * @param string $lowValue
     */
    public function setLowValue(string $lowValue): void
    {
        $this->lowValue = $lowValue;
    }

    /**
     * @return string
     */
    public function getHighValue(): string
    {
        return $this->highValue;
    }

    /**
     * @param string $highValue
     */
    public function setHighValue(string $highValue): void
    {
        $this->highValue = $highValue;
    }

    /**
     * @return string
     */
    public function getQuery() {

        $query = '';
        $andFilters = [];
        switch($this->getProperty()->getFieldType()) {
            case 'number_field':
                switch($this->getOperator()) {
                    case 'EQ':
                        $value = str_replace(',', '', $this->getValue());
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') = \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), $value);
                        }

                        break;
                    case 'NEQ':
                        $value = str_replace(',', '', $this->getValue());
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') != \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), $value);
                        }

                        break;
                    case 'LT':
                        $value = str_replace(',', '', $this->getValue());
                        if(trim($this->getValue()) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') < \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', null) < \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), $value, $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        }

                        break;
                    case 'GT':
                        $value = str_replace(',', '', $this->getValue());
                        if(trim($this->getValue()) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') > \'%s\' AND `%s`.properties->>\'$.%s\' != \'\' AND `%s`.properties->>\'$.%s\' IS NOT NULL', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), $value, $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        }
                        break;
                    case 'BETWEEN':
                        $lowValue = str_replace(',', '', $this->getLowValue());
                        $highValue = str_replace(',', '', $this->getHighValue());
                        if(trim($this->getLowValue()) === '' || trim($this->getHighValue()) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '', '');
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), $lowValue, $highValue);
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'single_line_text_field':
            case 'multi_line_text_field':
                switch($this->getOperator()) {
                    case 'EQ':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($this->getValue()));
                        }
                        break;
                    case 'NEQ':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($this->getValue()));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'date_picker_field':
                switch($this->getOperator()) {
                    case 'EQ':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') = STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getValue());
                        }
                        break;
                    case 'NEQ':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') != STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getValue());
                        }
                        break;
                    case 'LT':
                        if(trim($this->getValue()) === '') {
                            // TODO revisit this one. how do you compare less than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') < \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') < STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getValue());
                        }
                        break;
                    case 'GT':
                        if(trim($this->getValue()) === '') {
                            // TODO revisit this one. how do you compare greater than to an empty string? What should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') > \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') > STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getValue());
                        }
                        break;
                    case 'BETWEEN':
                        if(trim($this->getLowValue()) === '' || trim($this->getHighValue()) === '') {
                            // TODO revisit this one. IF the low value or high value is empty, what should we do? Right now this is just returning 0 results
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, `%s`.properties->>\'$.%s\', \'\') BETWEEN \'%s\' AND \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '', '');
                        } else {
                            $andFilters[] = sprintf('STR_TO_DATE(`%s`.properties->>\'$.%s\', \'%%m/%%d/%%Y\') BETWEEN STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\') AND STR_TO_DATE(\'%s\', \'%%m/%%d/%%Y\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getLowValue(), $this->getHighValue());
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'single_checkbox_field':

                switch($this->getOperator()) {
                    case 'IN':

                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\' OR IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '1', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '1');
                            }
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            if($values == ['0','1'] || $values == ['1','0']) {
                                $andFilters[] = sprintf('(IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\' AND IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\')', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '1', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '0');
                            } elseif ($values == ['0']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '0');
                            } elseif ($values == ['1']) {
                                $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), '1');
                            }
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'dropdown_select_field':
            case 'radio_select_field':
                switch($this->getOperator()) {
                    case 'IN':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') = \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') != \'%s\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)",implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
            case 'multiple_checkbox_field':
                switch($this->getOperator()) {
                    case 'IN':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') LIKE \'%%%s%%\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" OR ", $conditions));
                        }
                        break;
                    case 'NOT_IN':
                        if(trim($this->getValue()) === '') {
                            $andFilters[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), null) != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        } else {
                            $values = explode(',', $this->getValue());
                            $conditions = [];
                            foreach($values as $value) {
                                $conditions[] = sprintf('IF(`%s`.properties->>\'$.%s\' IS NOT NULL, LOWER(`%s`.properties->>\'$.%s\'), \'\') NOT LIKE \'%%%s%%\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName(), strtolower($value));
                            }
                            $andFilters[] = sprintf("(%s)", implode(" AND ", $conditions));
                        }
                        break;
                    case 'HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is not null AND `%s`.properties->>\'$.%s\' != \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                    case 'NOT_HAS_PROPERTY':
                        $andFilters[] = sprintf('`%s`.properties->>\'$.%s\' is null OR `%s`.properties->>\'$.%s\' = \'\'', $this->getAlias(), $this->getProperty()->getInternalName(), $this->getAlias(), $this->getProperty()->getInternalName());
                        break;
                }
                break;
        }

        // add the child filters (AND conditionals)
        /*    if(!empty($customFilter['childFilters'])) {
                foreach($customFilter['childFilters'] as $uid => $childFilter) {
                    $this->getAlias() = !empty($data['filters'][$uid]['alias']) ? $data['filters'][$uid]['alias'] : $this->getAlias();
                    $andFilters[] = $this->getConditionForReport($childFilter, $this->getAlias(), $data, true);
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
}