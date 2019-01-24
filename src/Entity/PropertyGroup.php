<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyGroupRepository")
 * @ORM\HasLifecycleCallbacks()
 * @CustomAssert\PropertyGroupDeletion(groups={"DELETE"})
 * @CustomAssert\PropertyGroupNameAlreadyExists(groups={"CREATE", "EDIT"})
 */
class PropertyGroup
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a name for your super cool Property Group!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $internalName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Property", mappedBy="propertyGroup", cascade={"persist", "remove"})
     */
    private $properties;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="propertyGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

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
}
