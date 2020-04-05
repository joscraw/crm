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
    const RECORD_CREATE = 'RECORD_CREATE';
    const RECORD_UPDATE = 'RECORD_UPDATE';
    /**#@-*/

    public static $triggers = [
      self::RECORD_CREATE => self::RECORD_CREATE,
      self::RECORD_UPDATE => self::RECORD_UPDATE
    ];
}