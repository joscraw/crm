<?php

namespace App\Entity;

use App\Model\AbstractField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\FormFieldProperties;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
 * @CustomAssert\PropertyGroupDoesntExist(groups={"CREATE", "EDIT"})
 * @CustomAssert\SystemDefined(groups={"FIRST"})
 */
class Property
{
    use TimestampableEntity;
    use FormFieldProperties;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES", "WORKFLOW_TRIGGER_DATA", "TRIGGER"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES", "WORKFLOW_TRIGGER_DATA", "TRIGGER"})
     *
     * @Assert\NotBlank(message="Don't forget a label for your new Property!", groups={"CREATE", "EDIT"})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Woah! Only use letters, numbers, underscores and spaces please!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $label;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES", "TRIGGER"})
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $internalName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES", "TRIGGER"})
     *
     * @Assert\NotBlank(message="Don't forget to select a field type for your new Property!", groups={"CREATE", "EDIT"})
     * @Assert\Choice(callback="getValidFieldTypes")
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $fieldType;

    /**
     * @Groups({"SELECTABLE_PROPERTIES"})
     *
     * @var AbstractField
     *
     * @Assert\Valid
     *
     * @ORM\Column(type="json", nullable=true)
     */
    protected $field;

    /**
     * @Assert\NotBlank(message="Don't forget to select a property group!", groups={"CREATE", "EDIT"})
     * @ORM\ManyToOne(targetEntity="App\Entity\PropertyGroup", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $propertyGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customObject;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "SELECTABLE_PROPERTIES"})
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $required = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isColumn = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $columnOrder;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isDefaultProperty = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $defaultPropertyOrder;

    /**
     * This flag is used for system defined properties which can't be deleted
     * @ORM\Column(type="boolean")
     */
    protected $systemDefined = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TriggerFilter", mappedBy="property")
     */
    private $triggerFilters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SetPropertyValueAction", mappedBy="property")
     */
    private $setPropertyValueActions;

    /**
     * This property is used to determine whether or not we show that property in
     * the create, edit, and list view for the properties
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hidden;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isUnique = false;

    public function __construct()
    {
        $this->triggerFilters = new ArrayCollection();
        $this->setPropertyValueActions = new ArrayCollection();
    }


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

    public function getId()
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

    public function isSystemDefined(): ?bool
    {
        return $this->systemDefined;
    }

    public function setSystemDefined(bool $systemDefined): self
    {
        $this->systemDefined = $systemDefined;

        return $this;
    }

    /**
     * @return Collection|TriggerFilter[]
     */
    public function getTriggerFilters(): Collection
    {
        return $this->triggerFilters;
    }

    public function addTriggerFilter(TriggerFilter $triggerFilter): self
    {
        if (!$this->triggerFilters->contains($triggerFilter)) {
            $this->triggerFilters[] = $triggerFilter;
            $triggerFilter->setProperty($this);
        }

        return $this;
    }

    public function removeTriggerFilter(TriggerFilter $triggerFilter): self
    {
        if ($this->triggerFilters->contains($triggerFilter)) {
            $this->triggerFilters->removeElement($triggerFilter);
            // set the owning side to null (unless already changed)
            if ($triggerFilter->getProperty() === $this) {
                $triggerFilter->setProperty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SetPropertyValueAction[]
     */
    public function getSetPropertyValueActions(): Collection
    {
        return $this->setPropertyValueActions;
    }

    public function addSetPropertyValueAction(SetPropertyValueAction $setPropertyValueAction): self
    {
        if (!$this->setPropertyValueActions->contains($setPropertyValueAction)) {
            $this->setPropertyValueActions[] = $setPropertyValueAction;
            $setPropertyValueAction->setProperty($this);
        }

        return $this;
    }

    public function removeSetPropertyValueAction(SetPropertyValueAction $setPropertyValueAction): self
    {
        if ($this->setPropertyValueActions->contains($setPropertyValueAction)) {
            $this->setPropertyValueActions->removeElement($setPropertyValueAction);
            // set the owning side to null (unless already changed)
            if ($setPropertyValueAction->getProperty() === $this) {
                $setPropertyValueAction->setProperty(null);
            }
        }

        return $this;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): self
    {
        $this->hidden = $hidden;

        return $this;
    }

    public function getIsUnique(): ?bool
    {
        return $this->isUnique;
    }

    public function setIsUnique(?bool $isUnique): self
    {
        $this->isUnique = $isUnique;

        return $this;
    }
}
