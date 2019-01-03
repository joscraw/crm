<?php

namespace App\Entity;

use App\Model\Content;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomObjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomObject
{

    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a label for your brand new sweeeeet Custom Object!")
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $label;

    /**
     * internal name
     *
     * @Assert\Regex("/^[a-zA-Z_]*$/", message="Woah! Only use letters and underscores please!")
     *
     * @ORM\Column(name="internal_name", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $internalName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Property", mappedBy="customObject", cascade={"persist", "remove"})
     */
    private $properties;

    public function __construct()
    {
        $this->properties = new ArrayCollection();
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
    public function setLabel(string $label): void
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
    public function setInternalName(string $internalName)
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
}
