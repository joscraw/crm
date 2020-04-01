<?php

namespace App\Model\Filter;

use App\Api\ApiProblemException;
use App\Entity\Property;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Filter
{
    use Uid;


    /**#@+
     * These are all the OPERATORS available for filtering
     * @var int
     */
    const EQ = 'EQ';
    const NEQ = 'NEQ';
    const LT = 'LT';
    const GT = 'GT';
    const LTE = 'LTE';
    const GTE = 'GTE';
    const CONTAINS = 'CONTAINS';
    const IN = 'IN';
    const NOT_IN = 'NOT_IN';
    const BETWEEN = 'BETWEEN';
    const HAS_PROPERTY = 'HAS_PROPERTY';
    const NOT_HAS_PROPERTY = 'NOT_HAS_PROPERTY';
    /**#@-*/

    protected $templates = array(
        self::EQ => ':prop = :value', // THIS IS WORKING PROPERLY!
        self::NEQ => ':prop != :value', // THIS IS WORKING PROPERLY!
        self::LT => ':prop < :value',
        self::GT => ':prop > :value',
        self::LTE => ':prop <= :value',
        self::GTE => ':prop >= :value',
        self::CONTAINS => ':prop LIKE :value',
        self::IN => ':prop IN (:multivalue)',
        self::NOT_IN => ':prop NOT IN (:multivalue)',
        self::BETWEEN => ':prop BETWEEN :low AND :high',
        self::HAS_PROPERTY => 'LENGTH(COALESCE(:prop, \'\')) > 0',
        self::NOT_HAS_PROPERTY => 'COALESCE(:prop, \'\') = \'\'',
    );

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
    public function getValue(): ?string
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
    public function getLowValue(): ?string
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
    public function getHighValue(): ?string
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

    public function validate() {

        $hasError = !array_key_exists($this->getOperator(), $this->templates);
        if ($hasError) {
            throw new ApiProblemException(400, sprintf(
                "Unsupported operator for property=%s (%s): %s",
                $this->getProperty()->getId(),
                $this->getProperty()->getFieldType(),
                $this->getOperator()));
        }

        $hasError = ($this->getProperty()->getFieldType() === 'number_field' &&
            $this->getOperator() == 'BETWEEN') && ( $this->getLowValue() === NULL ||
            $this->getLowValue() === '' || $this->getHighValue() === NULL || $this->getHighValue() === '');
        if($hasError) {
            throw new ApiProblemException(400, sprintf(
                "A lowValue and highValue must be set for property=%s (%s): %s",
                $this->getProperty()->getId(),
                $this->property->getFieldType(),
                $this->getOperator()));
        }

        $hasError = ($this->getProperty()->getFieldType() === 'number_field' &&
                $this->getOperator() != 'BETWEEN') && ( $this->getValue() === NULL ||
                $this->getValue() === '');
        if($hasError) {
            throw new ApiProblemException(400, sprintf(
                "A value must be set for property=%s (%s): %s",
                $this->getProperty()->getId(),
                $this->property->getFieldType(),
                $this->getOperator()));
        }
    }

    /**
     * Gets query for filter using binding for prepared statements
     * @param FilterData $filterData
     * @return string
     */
    public function getQuery(FilterData $filterData) {

        $context = [
            'type' => $this->getProperty()->getFieldType(),
            'operator' => strtoupper($this->getOperator()),
            'alias' => $this->getAlias(),
            'name' => sprintf('$."%s"', $this->getProperty()->getInternalName()),
            'value' => strtolower((string)$this->getValue()),
            'low' => strtolower((string)$this->getLowValue()),
            'high' => strtolower((string)$this->getHighValue()),
            'property_getter' => sprintf('LOWER(`%s`.properties->>?)', $this->getAlias()),
            'value_transformer' => '?'
        ];

        // todo need support for the new time field which I added recently
        if ($context['type'] == 'date_picker_field') {
            $context['property_getter'] = sprintf('STR_TO_DATE(`%s`.properties->>?, \'%%m/%%d/%%Y\')', $context['alias']);
            $context['value_transformer'] = 'STR_TO_DATE(?, \'%m/%d/%Y\')';
        }

        if ($context['type'] == 'time') {
            $context['property_getter'] = sprintf('STR_TO_DATE(`%s`.properties->>?, \'%%h%%i\')', $context['alias']);
            $context['value_transformer'] = 'STR_TO_DATE(?, \'%h%i\')';
        }

        if ($context['type'] == 'number_field') {
            // TODO do we have to type hint here? I know that the binding logic needs to know if it's a number, bool, string, etc
            //  but maybe there is a better way.
            $context['value'] = (int) $context['value'];
            $context['low'] = (int) $context['low'];
            $context['high'] = (int) $context['high'];

            // The property getter for numbers has to be a little different due to non existent properties being evaluated by
            // the MYSQL Engine as being less than any given number where as NULL is never considered less than a number.
            $context['property_getter'] = sprintf('IF(`%s`.properties->>? = \'\', NULL, `%s`.properties->>?)', $context['alias'], $context['alias']);
        }

        if ($context['operator'] == 'CONTAINS') {
            $context['value'] = sprintf("%%%s%%", $context['value']);
        }

        // todo 1. go through each comparison and test
        //  to through each join and test
        //  go through the different field types and test
        //  take a look at the getQuery(FilterData $filterData) method below and make sure this function isn't missing any checks ex: if null, etc
        //  test the HAS PROPERTY AND NOT HAS PROPERTY OPTIONS BELOW


        // todo some templates don't work with certain field types. How do I address this?
        $templates = array(
            'EQ' => ':prop = :value', // THIS IS WORKING PROPERLY!
            'NEQ' => ':prop != :value', // THIS IS WORKING PROPERLY!
            'LT' => ':prop < :value', // THIS IS WORKING PROPERLY
            'GT' => ':prop > :value', // THIS IS WORKING PROPERLY
            'LTE' => ':prop <= :value', // THIS IS WORKING PROPERLY
            'GTE' => ':prop >= :value', // THIS IS WORKING PROPERLY
            'CONTAINS' => ':prop LIKE :value', // THIS IS WORKING PROPERLY
            'IN' => ':prop IN (:multivalue)', // THIS IS WORKING PROPERLY
            'NOT_IN' => ':prop NOT IN (:multivalue)', // THIS IS WORKING PROPERLY
            'BETWEEN' => ':prop BETWEEN :low AND :high', // THIS IS WORKING PROPERLY
            'HAS_PROPERTY' => 'LENGTH(COALESCE(:prop, \'\')) > 0',
            'NOT_HAS_PROPERTY' => 'COALESCE(:prop, \'\') = \'\'',
        );

        $template = $templates[$context['operator']];

        $bindings = [];

        $template = preg_replace_callback('/(\:[a-zA-Z\-\_]+)/', function($matches) use($context, &$bindings) {
            $slug = $matches[0];
            switch($slug) {
                case ':prop':
                    $bindings = array_merge($bindings, array_fill(0, substr_count($context['property_getter'], '?'), $context['name']));
                    return $context['property_getter'];
                case ':multivalue':
                    // Note: This assumes value_transformer binds :value exactly 1 time
                    $tokens = array_map(function($e) { return trim($e); }, explode(',', $context['value']));
                    $bindings = array_merge($bindings, $tokens);
                    return str_repeat($context['value_transformer'].',', count($tokens) - 1) . $context['value_transformer'];
                case ':value':
                case ':low':
                case ':high':
                    $bindings = array_merge($bindings, array_fill(0, substr_count($context['value_transformer'], '?'), $context[str_replace(':', '', $slug)]));
                    return $context['value_transformer'];
                default:
                    return $slug;
            }
        }, $template);

        $filterData->filterQueries[$this->getUid()] = ['sql' => $template, 'bindings' => $bindings];
    }
}