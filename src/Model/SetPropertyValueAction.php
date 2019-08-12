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
    protected static $name = 'set_property_value_action';

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
}