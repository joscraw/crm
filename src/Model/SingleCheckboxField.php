<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class SingleCheckboxField
 * @package App\Model
 */
class SingleCheckboxField extends AbstractField
{
    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $name = FieldCatalog::SINGLE_CHECKBOX;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Single checkbox field';

}