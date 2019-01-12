<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "single_line_text_field"="App\Model\SingleLineTextField",
 *    "multi_line_text_field"="App\Model\MultiLineTextField",
 *    "dropdown_select_field"="App\Model\DropdownSelectField",
 *    "single_checkbox_field"="App\Model\SingleCheckboxField",
 *    "multiple_checkbox_field"="App\Model\MultipleCheckboxField",
 *    "radio_select_field"="App\Model\RadioSelectField",
 *    "number_field"="App\Model\NumberField",
 *    "date_picker_field"="App\Model\DatePickerField",
 *    "custom_object_field"="App\Model\CustomObjectField"
 * })
 */
abstract class AbstractField implements JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = 'abstract_field';

    /**
     * @var string
     */
    protected static $description = 'Abstract Field';

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [];
    }

    /**
     * @return string
     */
    final public static function getName()
    {
        return static::$name;
    }

    /**
     * @return string
     */
    final public static function getDescription()
    {
        return static::$description;
    }
}