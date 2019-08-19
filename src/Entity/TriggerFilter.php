<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TriggerFilterRepository")
 */
class TriggerFilter
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"TRIGGER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $operator;

    /**
     * @Groups({"TRIGGER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @Groups({"TRIGGER"})
     * @ORM\Column(type="array")
     */
    private $joins = [];

    /**
     * @Groups({"TRIGGER"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referencedFilterPath;

    /**
     * @Groups({"TRIGGER"})
     * @ORM\Column(type="array")
     */
    private $andFilters = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Property", inversedBy="triggerFilters")
     */
    private $property;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PropertyTrigger", inversedBy="triggerFilters")
     */
    private $propertyTrigger;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getJoins(): ?array
    {
        return $this->joins;
    }

    public function setJoins(array $joins): self
    {
        $this->joins = $joins;

        return $this;
    }

    public function getReferencedFilterPath(): ?string
    {
        return $this->referencedFilterPath;
    }

    public function setReferencedFilterPath(?string $referencedFilterPath): self
    {
        $this->referencedFilterPath = $referencedFilterPath;

        return $this;
    }

    public function getAndFilters(): ?array
    {
        return $this->andFilters;
    }

    public function setAndFilters(array $andFilters): self
    {
        $this->andFilters = $andFilters;

        return $this;
    }

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getPropertyTrigger(): ?PropertyTrigger
    {
        return $this->propertyTrigger;
    }

    public function setPropertyTrigger(?PropertyTrigger $propertyTrigger): self
    {
        $this->propertyTrigger = $propertyTrigger;

        return $this;
    }
}
