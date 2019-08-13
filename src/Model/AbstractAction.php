<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "PROPERTY_VALUE_ACTION"="App\Model\SetPropertyValueAction"
 * })
 */
abstract class AbstractAction
{

    const PROPERTY_VALUE_ACTION = 'PROPERTY_VALUE_ACTION';

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @var string
     */
    protected $uid;

    /**
     * @var string
     */
    protected static $name = 'abstract_workflow_action';

    /**
     * @var string
     */
    protected static $description = 'Abstract Workflow action';

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