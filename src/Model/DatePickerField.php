<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class DatePickerField
 * @package App\Model
 */
class DatePickerField extends AbstractField
{
    /**
     * Used in the DateFieldType form and in the Transformer for the deserializer. This format
     * matches the format set on the front end with the DatePicker jQuery plugin
     * @var string
     */
    public static $displayFormat = 'mm-dd-yyyy';

    /**
     * Needs to be stored in different format  mysql to be able to CAST() into a DateTime Format
     * @var string
     */
    public static $storedFormat = 'Y-m-d';

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $name = FieldCatalog::DATE_PICKER;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Date picker field';

}