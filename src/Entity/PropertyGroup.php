<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyGroupRepository")
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
     * @Assert\NotBlank(message="Don't forget a name for your super cool Property Group!")
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Property", mappedBy="propertyGroup", cascade={"persist", "remove"})
     */
    private $property;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(Property $property): self
    {
        $this->property = $property;

        // set the owning side of the relation if necessary
        if ($this !== $property->getPropertyGroup()) {
            $property->setPropertyGroup($this);
        }

        return $this;
    }
}
