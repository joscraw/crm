<?php

namespace App\Model;

use App\Entity\CustomObject;

/**
 * Class CustomObjectField
 * @package App\Model
 */
class CustomObjectField extends AbstractField implements \JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = FieldCatalog::CUSTOM_OBJECT;

    /**
     * @var string
     */
    protected static $description = 'Custom object field';

    /**
     * @var CustomObject
     */
    private $customObject;

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $name = "Josh";
        return array_merge(
            parent::jsonSerialize(),
            [
                'name'          => $this->getName(),
                'description'   => $this->getDescription(),
                'customObject'  => $this->getCustomObject()
            ]
        );
    }

    /**
     * @return CustomObject
     */
    public function getCustomObject()
    {
        return $this->customObject;
    }

    /**
     * @param CustomObject $customObject
     * @return $this
     */
    public function setCustomObject(CustomObject $customObject)
    {
        $this->customObject = $customObject;

        return $this;
    }
}