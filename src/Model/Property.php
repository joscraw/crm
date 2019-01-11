<?php

namespace App\Model;

use App\Entity\PropertyGroup;
use JsonSerializable;

/**
 * Class Property
 * @package App\Model
 */
class Property implements JsonSerializable
{
    protected $label;

    /**
     * @var string
     */
    protected $internalName;

    /**
     * @var
     */
    protected $description;

    /**
     * @var PropertyGroup
     */
    protected $group;

    /**
     * @var string
     */
    protected $fieldType;

    /**
     * @var AbstractField
     */
    protected $field;

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
    public function getType()
    {
        return static::class;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getInternalName(): string
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     */
    public function setInternalName(string $internalName)
    {
        $this->internalName = $internalName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return PropertyGroup
     */
    public function getGroup(): PropertyGroup
    {
        return $this->group;
    }

    /**
     * @param PropertyGroup $group
     */
    public function setGroup(PropertyGroup $group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getFieldType(): string
    {
        return $this->fieldType;
    }

    /**
     * @param string $fieldType
     */
    public function setFieldType(string $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * @return AbstractField
     */
    public function getField(): AbstractField
    {
        return $this->field;
    }

    /**
     * @param AbstractField $field
     */
    public function setField(AbstractField $field)
    {
        $this->field = $field;
    }
}