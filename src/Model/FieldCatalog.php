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
    const SINGLE_LINE_TEXT = 'single_line_text_field';
    const MULTI_LINE_TEXT = 'multi_line_text_field';
    const DROPDOWN_SELECT = 'dropdown_select_field';
    const SINGLE_CHECKBOX = 'single_checkbox_field';
    const MULTIPLE_CHECKBOX = 'multiple_checkbox_field';
    const RADIO_SELECT = 'radio_select_field';
    const NUMBER = 'number_field';
    const DATE_PICKER = 'date_picker_field';
    /**#@-*/

    /***
     * List of field types mostly used for select form drop downs
     *
     * @var array
     */
    private static $fields = [
        'Single-line text' => self::SINGLE_LINE_TEXT,
        'Multi-line text' => self::MULTI_LINE_TEXT,
        'Dropdown select' => self::DROPDOWN_SELECT,
        'Single checkbox' => self::SINGLE_CHECKBOX,
        'Multiple Checkbox' => self::MULTIPLE_CHECKBOX,
        'Radio select' => self::RADIO_SELECT,
        'Number' => self::NUMBER,
        'Date picker' => self::DATE_PICKER
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
    public static function isValidField($fieldType)
    {
        return array_key_exists($fieldType, self::$fields);
    }

    /**
     * Return list of fields
     *
     * @return array
     */
    public static function getFields() {
        return self::$fields;
    }
}
