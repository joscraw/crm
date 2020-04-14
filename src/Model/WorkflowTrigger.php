<?php

namespace App\Model;

/**
 * Class WorkflowTrigger
 * @package App\Model
 */
class WorkflowTrigger
{
    /**#@+
     * These are the triggers for all the automations/business rules (workflows)
     * @var int
     */
    const PROPERTY_TRIGGER = 'PROPERTY_TRIGGER';
    /**#@-*/

    public static $triggers = [
      self::PROPERTY_TRIGGER => self::PROPERTY_TRIGGER
    ];
}