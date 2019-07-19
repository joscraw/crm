<?php

namespace App\Model;

use App\Entity\CustomObject;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Property;

/**
 * Class PropertyBasedTrigger
 * @package App\Model
 */
class PropertyBasedTrigger extends AbstractWorkflowTrigger
{
    const CONDITION_CONTAINS_EXACTLY = 'CONTAINS_EXACTLY';
    const CONDITION_DOESNT_CONTAIN_EXACTLY = 'DOESNT_CONTAIN_EXACTLY';
    const CONDITION_IS_KNOWN = 'IS_KNOWN';
    const CONDITION_IS_UNKNOWN = 'IS_UNKNOWN';

    public static $availableConditions = [
        'Contains exactly' => 'CONTAINS_EXACTLY',
        'Doesn\'t contain exactly' => 'DOESNT_CONTAIN_EXACTLY',
        'Is known' => 'IS_KNOWN',
        'Is Unknown' => 'IS_UNKNOWN'
    ];

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     *
     * @var string
     */
    protected static $name = 'property_based_trigger';

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     * @var string
     */
    protected static $description = 'Property based trigger';

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     * @var CustomObject
     */
    protected $customObject;

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     *
     * @var Property
     */
    protected $property;

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     * @var AbstractCondition
     */
    protected $condition;

    /**
     * @return CustomObject
     */
    public function getCustomObject()
    {
        return $this->customObject;
    }

    /**
     * @param CustomObject $customObject
     * @return $this
     */
    public function setCustomObject(CustomObject $customObject = null)
    {
        $this->customObject = $customObject;

        return $this;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     * @return PropertyBasedTrigger
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }
}