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
     *
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @Assert\Regex("/^[a-zA-Z_]*$/", message="Woah! Only use letters and underscores please!")
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
     * @var DropdownSelectField
     *
     * @Assert\Valid
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $field;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\PropertyGroup", inversedBy="property")
     * @ORM\JoinColumn(nullable=false)
     */
    private $propertyGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\CustomObject", inversedBy="property")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    public static function getValidFieldTypes()
    {
        return FieldCatalog::getFields();
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

    public function getField(): ?AbstractField
    {
        return $this->field;
    }

    public function setField(AbstractField $field): self
    {
        $this->field = $field;

        return $this;
    }

}
