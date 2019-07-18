<?php

namespace App\Model;

use RuntimeException;

/**
 * Class WorkflowTriggerCatalog
 *
 * List of valid workflow triggers
 *
 * @package App\Model
 */
class WorkflowTriggerCatalog
{
    /**#@+
     * @var string
     */
    const PROPERTY_BASED_TRIGGER = 'property_based_trigger';
    /**#@-*/

    /***
     * List of field triggers and options
     *
     * @var array
     */
    public static $triggers = [
        self::PROPERTY_BASED_TRIGGER => [
            'description' => 'Property based trigger',
            'friendly_name' => 'Property based trigger'
        ]
    ];

    /**
     * Constructor
     *
     * Class is not intended to be implemented
     */
    private function __construct()
    {
        throw new RuntimeException("Can't get there from here");
    }

    /**
     * Check for valid trigger
     *
     * @param $trigger
     * @return bool
     */
    public static function isValidTrigger($trigger)
    {
        return array_key_exists($trigger, self::$triggers);
    }

    /**
     * @return array
     */
    public static function getValidTriggers() {

        return array_keys(self::$triggers);
    }

    /**
     * Return list of triggers and their descriptions/options
     *
     * @return array
     */
    public static function getTriggers() {
        return self::$triggers;
    }
}
