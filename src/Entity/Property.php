<?php

namespace App\Entity;

use App\Model\AbstractField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({"App\EntityListener\PropertyListener"})
 */
class Property
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a label for your new Property!")
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Woah! Only use letters, numbers, underscores and spaces please!")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $internalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @Assert\NotBlank(message="Don't forget to select a field type for your new Property!")
     * @Assert\Choice(callback="getValidFieldTypes")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $fieldType;

    /**
     * @var AbstractField
     *
     * @Assert\Valid
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $field;

    /**
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
     * @ORM\PrePersist
     */
    public function setInternalNameValue()
    {
        if(!$this->internalName) {
            $this->internalName = strtolower(
                preg_replace('/\s+/', '_', $this->getLabel())
            );
        }
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

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function setInternalName(string $internalName): self
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

    public function setPropertyGroup(PropertyGroup $propertyGroup): self
    {
        $this->propertyGroup = $propertyGroup;

        return $this;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(CustomObject $customObject): self
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
}
