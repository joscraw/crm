<?php

namespace App\Model;

use RuntimeException;

/**
 * Class FieldCatalog
 *
 * List of valid field types
 *
 * @package App\Model
 */
class FieldCatalog
{
    /**#@+
     * @var string
     */
    const SINGLE_LINE_TEXT = 'single_line_text';
    /**#@-*/

    /**
     * List of valid field types
     *
     * @var array
     */
    private static $validFieldTypes = [
        self::SINGLE_LINE_TEXT => [
            'shortDescription' => 'Single Line Text',
            'description'      => 'Single line text field.',
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
     * Check for valid field type
     *
     * @param $fieldType
     * @return bool
     */
    public static function isValidInteraction($fieldType)
    {
        return array_key_exists($fieldType, self::$validFieldTypes);
    }

    /**
     * Return list of valid field types
     *
     * @return array
     */
    public static function getFieldTypes()
    {
        return self::$validFieldTypes;
    }
}
