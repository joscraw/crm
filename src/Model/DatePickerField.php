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