<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "single_line_text_field"="App\Model\SingleLineTexField",
 *    "dropdown_select_field"="App\Model\DropdownSelectField"
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