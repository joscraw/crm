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
    const CUSTOM_OBJECT = 'custom_object_field';
    /**#@-*/

    /***
     * List of field types and options
     *
     * @var array
     */
    public static $fields = [
        self::SINGLE_LINE_TEXT => [
            'description' => 'Stores a string of any alphanumeric characters, such as a word, a phrase, or a sentence.',
            'friendly_name' => 'Single line text'
        ],
        self::MULTI_LINE_TEXT => [
            'description' => 'Stores multiple strings of alphanumeric characters, such as a paragraph or list of items.',
            'friendly_name' => 'Multi line text'
        ],
        self::DROPDOWN_SELECT => [
            'description' => 'Stores an unlimited number of options, and only one option can be selected as a value. In forms, they appear as a select field.',
            'friendly_name' => 'Dropdown select'
        ],
        self::SINGLE_CHECKBOX => [
            'description' => 'Stores two options, on or off. Often used if you need a property value that is strictly true or false. In the CRM they appear as a Dropdown Select. In forms, they appear as a single checkbox.',
            'friendly_name' => 'Single Checkbox'
            ],
        self::MULTIPLE_CHECKBOX => [
            'description' => 'Stores checkboxes that contain several, usually related options, with an unlimited number of options. In the CRM they appear as a Dropdown Select. In forms, they appear as checkboxes.',
            'friendly_name' => 'Multiple checkbox'
        ],
        self::RADIO_SELECT => [
            'description' => 'Stores an unlimited number of options, and only one option can be selected as a value. In the CRM they appear as a Dropdown Select. In forms, they appear as radio fields.',
            'friendly_name' => 'Radio select'
        ],
        self::NUMBER => [
            'description' => 'Stores a string of numerals or numbers written in decimal or scientific notation. You cannot use decimals or negative numbers in your filters when segmenting lists or workflows by number properties.',
            'friendly_name' => 'Number'
        ],
        self::DATE_PICKER => [
            'description' => 'Stores a date value. In forms, they are used to allow visitors to input a specific date in a standard format, ensuring no confusion between the day and month when inputting the date.',
            'friendly_name' => 'Date picker'
        ],
        self::CUSTOM_OBJECT=> [
            'description' => 'Stores a reference to another custom object.',
            'friendly_name' => 'Custom object'
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
    public static function isValidField($fieldType)
    {
        return array_key_exists($fieldType, self::$fields);
    }

    /**
     * @return array
     */
    public static function getValidFieldTypes() {

        return array_keys(self::$fields);
    }

    /**
     * Return list of fields and their descriptions/options
     *
     * @return array
     */
    public static function getFields() {
        return self::$fields;
    }

    /**
     * @return array
     */
    public static function getOptionsForChoiceTypeField() {
        $choices = [];
        foreach(self::$fields as $field_name => $options) {
            $choices[$options['friendly_name']] = $field_name;
        }
        return $choices;
    }

    /**
     * @param $fieldType
     * @return mixed|null
     */
    public static function getOptionsForFieldType($fieldType) {
        return array_key_exists($fieldType, self::$fields) ? self::$fields[$fieldType] : null;
    }
}
