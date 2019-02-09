<?php

namespace App\Entity;

use App\Model\Content;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomObjectRepository")
 * @ORM\HasLifecycleCallbacks()
 * @CustomAssert\CustomObjectLabelAlreadyExists(groups={"CREATE", "EDIT"})
 * @CustomAssert\CustomObjectInternalNameAlreadyExists(groups={"CREATE", "EDIT"})
 * @CustomAssert\CustomObjectDeletion(groups={"DELETE"})
 */
class CustomObject /*implements \JsonSerializable*/
{

    use TimestampableEntity;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER"})
     * @Assert\NotBlank(message="Don't forget a label for your super cool sweeeeet Custom Object!", groups={"CREATE", "EDIT"})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Woah! Only use letters, numbers, underscores and spaces please!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $label;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER", "PROPERTIES_FOR_FILTER"})
     *
     * internal name
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!", groups={"CREATE"})
     *
     * @ORM\Column(name="internal_name", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $internalName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Property", mappedBy="customObject", cascade={"remove"})
     */
    private $properties;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PropertyGroup", mappedBy="customObject", cascade={"remove"})
     */
    private $propertyGroups;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="customObjects")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Record", mappedBy="customObject", orphanRemoval=true)
     */
    private $records;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
        $this->propertyGroups = new ArrayCollection();
        $this->records = new ArrayCollection();
    }

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

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;
    }

    /**
     * @return Collection|Property[]
     */
    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function addProperty(Property $property): self
    {
        if (!$this->properties->contains($property)) {
            $this->properties[] = $property;
            $property->setCustomObject($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->contains($property)) {
            $this->properties->removeElement($property);
            // set the owning side to null (unless already changed)
            if ($property->getCustomObject() === $this) {
                $property->setCustomObject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PropertyGroup[]
     */
    public function getPropertyGroups(): Collection
    {
        return $this->propertyGroups;
    }

    public function addPropertyGroup(PropertyGroup $propertyGroup): self
    {
        if (!$this->propertyGroups->contains($propertyGroup)) {
            $this->propertyGroups[] = $propertyGroup;
            $propertyGroup->setCustomObject($this);
        }

        return $this;
    }

    public function removePropertyGroup(PropertyGroup $propertyGroup): self
    {
        if ($this->propertyGroups->contains($propertyGroup)) {
            $this->propertyGroups->removeElement($propertyGroup);
            // set the owning side to null (unless already changed)
            if ($propertyGroup->getCustomObject() === $this) {
                $propertyGroup->setCustomObject(null);
            }
        }

        return $this;
    }

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    /**
     * @return Collection|Record[]
     */
    public function getRecords(): Collection
    {
        return $this->records;
    }

    public function addRecord(Record $record): self
    {
        if (!$this->records->contains($record)) {
            $this->records[] = $record;
            $record->setCustomObject($this);
        }

        return $this;
    }

    public function removeRecord(Record $record): self
    {
        if ($this->records->contains($record)) {
            $this->records->removeElement($record);
            // set the owning side to null (unless already changed)
            if ($record->getCustomObject() === $this) {
                $record->setCustomObject(null);
            }
        }

        return $this;
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
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'internalName' => $this->getInternalName()
        ];
    }*/

    public function setId($id) {
        $this->id = $id;
    }
}
