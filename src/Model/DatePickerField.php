<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class DatePickerField
 * @package App\Model
 */
class DatePickerField extends AbstractField
{
    const DATETIME = 'DATETIME';

    const TIME = 'TIME';

    /**
     * Used in the form
     * @var array
     */
    public static $types = [
        'Date Picker Field' => self::DATETIME,
        'Time Picker Field' => self::TIME,
    ];

    /**
     * Used in the DateFieldType form and in the Transformer for the deserializer. This format
     * matches the format set on the front end with the DatePicker jQuery plugin
     * @var string
     */
    public static $displayFormat = 'm/d/Y';

    /**
     * Needs to be stored in different format  mysql to be able to CAST() into a DateTime Format
     * @var string
     */
    public static $storedFormat = 'm/d/Y';

    /**
     * Used in the TimeFieldType form and in the Transformer for the deserializer.
     * @var string
     */
    public static $timeDisplayFormat = 'h:i A';

    /**
     * Needs to be stored in different format  mysql to be able to CAST() into a DateTime Format
     * @var string
     */
    public static $timeStoredFormat = 'h:i A';

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

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected $type = self::DATETIME;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function isDateField() {
        return $this->type === self::DATETIME;
    }

    public function isTimeField() {
        return $this->type === self::TIME;
    }
}