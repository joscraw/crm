<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "PROPERTY_BASED_TRIGGER"="App\Model\PropertyBasedTrigger"
 * })
 */
abstract class AbstractWorkflowTrigger implements JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = 'abstract_workflow_field';

    /**
     * @var string
     */
    protected static $description = 'Abstract Workflow Trigger';

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