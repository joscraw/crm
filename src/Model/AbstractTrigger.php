<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "PROPERTY_BASED_TRIGGER"="App\Model\PropertyTrigger"
 * })
 */
abstract class AbstractTrigger
{
    const PROPERTY_BASED_TRIGGER = 'PROPERTY_BASED_TRIGGER';

    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected $uid;

    /**
     * @var string
     */
    protected static $name = 'abstract_workflow_field';

    /**
     * @var string
     */
    protected static $description = 'Abstract Workflow Trigger';

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

    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
}