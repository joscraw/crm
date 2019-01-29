<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class NumberField
 * @package App\Model
 */
class NumberField extends AbstractField
{
    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $name = FieldCatalog::NUMBER;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Number field';

}