<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 * @CustomAssert\PropertyGroupDeletion(groups={"DELETE"})
 * @CustomAssert\PropertyGroupNameAlreadyExists(groups={"CREATE", "EDIT"})
 * @CustomAssert\SystemDefined(groups={"FIRST"})
 */
class PropertyGroup
{
    use TimestampableEntity;

    /**
     * @Groups({"SELECTABLE_PROPERTIES"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"SELECTABLE_PROPERTIES"})
     *
     * @Assert\NotBlank(message="Don't forget a name for your super cool Property Group!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"SELECTABLE_PROPERTIES"})
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $internalName;

    /**
     * @Groups({"SELECTABLE_PROPERTIES"})
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Property", mappedBy="propertyGroup", cascade={"remove"})
     */
    private $properties;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="propertyGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @ORM\Column(type="boolean")
     */
    private $systemDefined = false;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            preg_replace('/\s+/', '_', $this->getName())
        );

    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
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
            $property->setPropertyGroup($this);
        }

        return $this;
    }

    public function removeProperty(Property $property): self
    {
        if ($this->properties->contains($property)) {
            $this->properties->removeElement($property);
            // set the owning side to null (unless already changed)
            if ($property->getPropertyGroup() === $this) {
                $property->setPropertyGroup(null);
            }
        }

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

    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    public function setInternalName(?string $internalName): self
    {
        $this->internalName = $internalName;

        return $this;
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
}
