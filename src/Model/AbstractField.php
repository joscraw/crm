<?php

namespace App\Model;

use JsonSerializable;

/**
 * Class AbstractField
 * @package App\Model
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