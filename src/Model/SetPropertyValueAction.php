<?php

namespace App\Model;

use App\Entity\CustomObject;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Property;

/**
 * Class SetPropertyValueAction
 * @package App\Model
 */
class SetPropertyValueAction extends AbstractAction
{
    /**
     * @Groups({"WORKFLOW_ACTION"})
     *
     * @var string
     */
    protected static $name = AbstractAction::PROPERTY_VALUE_ACTION;

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @var string
     */
    protected static $description = 'Set property value';

    /**
     * @Groups({"WORKFLOW_ACTION"})
     *
     * @var Property
     */
    protected $property;

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @var string
     */
    protected $value;

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @var array
     */
    protected $joins = [];

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @var string
     */
    protected $operator;

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     * @return SetPropertyValueAction
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
}