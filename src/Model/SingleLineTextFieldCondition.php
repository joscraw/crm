<?php

namespace App\Model;

use App\Entity\CustomObject;
use App\Entity\WorkflowTrigger;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Property;

/**
 * Class SingleLineTextFieldCondition
 * @package App\Model
 */
class SingleLineTextFieldCondition extends AbstractCondition
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
     * @var string
     */
    protected static $name = 'single_line_text_field_condition';

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     * @var string
     */
    protected $operator;

    /**
     * @Groups({"WORKFLOW_TRIGGER_DATA"})
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param $operator
     * @return SingleLineTextFieldCondition
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

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
     * @return SingleLineTextFieldCondition
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}