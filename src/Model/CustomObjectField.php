<?php

namespace App\Model;

use App\Entity\CustomObject;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Property;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * Class CustomObjectField
 * @package App\Model
 */
class CustomObjectField extends AbstractField /*implements \JsonSerializable*/
{
    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES"})
     * @var string
     */
    protected static $name = FieldCatalog::CUSTOM_OBJECT;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Custom object field';

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES"})
     *
     * @Assert\NotBlank(message="Don't forget to select a custom object!", groups={"CREATE", "EDIT"})
     * @var CustomObject
     */
    protected $customObject;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var boolean
     */
    protected $multiple = false;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     *
     * When searching for a record to assign to this field when using selectize.js
     * you have the ability to control what properties you see back
     * in the search results response. This allows for a more intuitive search
     *
     * @var Property[] $selectizeSearchResultProperties
     */
    protected $selectizeSearchResultProperties;

    public function __construct()
    {
        $this->selectizeSearchResultProperties = new ArrayCollection();
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
/*    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [

                'name'          => $this->getName(),
                'description'   => $this->getDescription(),
                'customObject'  => $this->getCustomObject(),
                'multiple'      => $this->isMultiple(),
                'selectizeSearchResultProperties' => $this->getSelectizeSearchResultProperties()->toArray()
            ]
        );
    }*/

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
    public function setCustomObject(CustomObject $customObject = null)
    {
        $this->customObject = $customObject;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): ?bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     * @return CustomObjectField
     */
    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getSelectizeSearchResultProperties()
    {
        return $this->selectizeSearchResultProperties;
    }

    /**
     * @param ArrayCollection $selectizeSearchResultProperties
     * @return CustomObjectField
     */
    public function setSelectizeSearchResultProperties($selectizeSearchResultProperties): self
    {
        $this->selectizeSearchResultProperties = $selectizeSearchResultProperties;

        return $this;
    }

    /**
     * @param Property $selectizeSearchResultProperty
     * @return CustomObjectField
     */
    public function addSelectizeSearchResultProperty(Property $selectizeSearchResultProperty): self
    {
        if (!$this->selectizeSearchResultProperties->contains($selectizeSearchResultProperty)) {
            $this->selectizeSearchResultProperties[] = $selectizeSearchResultProperty;
        }
        return $this;
    }

    /**
     * @param Property $selectizeSearchResultProperty
     * @return CustomObjectField
     */
    public function removeSelectizeSearchResultProperty(Property $selectizeSearchResultProperty): self
    {
        if ($this->selectizeSearchResultProperties->contains($selectizeSearchResultProperty)) {
            $this->selectizeSearchResultProperties->removeElement($selectizeSearchResultProperty);
        }

        return $this;
    }


}