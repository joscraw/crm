<?php

namespace App\Entity;

use App\Model\AbstractField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"App\EntityListener\PropertyListener"})
 * @CustomAssert\PropertyInternalNameAlreadyExists(groups={"CREATE", "EDIT"})
 * @CustomAssert\PropertyLabelAlreadyExists(groups={"CREATE", "EDIT"})
 * @CustomAssert\ChoiceField(groups={"CREATE", "EDIT"})
 */
class Property
{
    use TimestampableEntity;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER", "PROPERTIES_FOR_REPORT"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER", "PROPERTIES_FOR_REPORT"})
     *
     * @Assert\NotBlank(message="Don't forget a label for your new Property!", groups={"CREATE", "EDIT"})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Woah! Only use letters, numbers, underscores and spaces please!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER", "PROPERTIES_FOR_REPORT"})
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $internalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER", "PROPERTIES_FOR_REPORT"})
     *
     * @Assert\NotBlank(message="Don't forget to select a field type for your new Property!", groups={"CREATE", "EDIT"})
     * @Assert\Choice(callback="getValidFieldTypes")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $fieldType;

    /**
     * @Groups({"PROPERTIES_FOR_FILTER", "PROPERTIES_FOR_REPORT"})
     *
     * @var AbstractField
     *
     * @Assert\Valid
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $field;

    /**
     * @Assert\NotBlank(message="Don't forget to select a property group!")
     * @ORM\ManyToOne(targetEntity="App\Entity\PropertyGroup", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $propertyGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $required = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isColumn = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $columnOrder;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDefaultProperty = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $defaultPropertyOrder;


    /**
     * @ORM\PrePersist
     */
    public function setInternalNameValue()
    {
        if(!$this->internalName) {
            $this->internalName = $this->getInternalNameValue();
        }
    }

    /**
     * @return string
     */
    public function getInternalNameValue()
    {
        return strtolower(
            preg_replace('/\s+/', '_', $this->getLabel())
        );

    }

    public static function getValidFieldTypes()
    {
        return FieldCatalog::getValidFieldTypes();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function setInternalName(?string $internalName): self
    {
        $this->internalName = $internalName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    public function setFieldType(?string $fieldType): self
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    public function getPropertyGroup(): ?PropertyGroup
    {
        return $this->propertyGroup;
    }

    public function setPropertyGroup(?PropertyGroup $propertyGroup): self
    {
        $this->propertyGroup = $propertyGroup;

        return $this;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    /**
     * @return AbstractField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param $field
     * @return Property
     */
    public function setField($field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     */
    public function setRequired(?bool $required): void
    {
        $this->required = $required;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
 /*   public function jsonSerialize()
    {
       return [
           'id' => $this->getId(),
           'internalName' => $this->getInternalName(),
           'label' => $this->getLabel(),
           'fieldType' => $this->getFieldType()
       ];
    }*/

    public function setId($id) {
        $this->id = $id;
    }

    public function getIsColumn(): ?bool
    {
        return $this->isColumn;
    }

    public function setIsColumn(?bool $isColumn): self
    {
        $this->isColumn = $isColumn;

        return $this;
    }

    public function getColumnOrder(): ?int
    {
        return $this->columnOrder;
    }

    public function setColumnOrder(?int $columnOrder): self
    {
        $this->columnOrder = $columnOrder;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDefaultProperty()
    {
        return $this->isDefaultProperty;
    }

    /**
     * @param mixed $isDefaultProperty
     */
    public function setIsDefaultProperty($isDefaultProperty): void
    {
        $this->isDefaultProperty = $isDefaultProperty;
    }

    /**
     * @return mixed
     */
    public function getDefaultPropertyOrder()
    {
        return $this->defaultPropertyOrder;
    }

    /**
     * @param mixed $defaultPropertyOrder
     */
    public function setDefaultPropertyOrder($defaultPropertyOrder): void
    {
        $this->defaultPropertyOrder = $defaultPropertyOrder;
    }
}
