<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class MultiLineTextField
 * @package App\Model
 */
class MultiLineTextField extends AbstractField
{
    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $name = FieldCatalog::MULTI_LINE_TEXT;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Multi line text field';

}