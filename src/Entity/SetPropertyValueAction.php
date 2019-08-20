<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SetPropertyValueActionRepository")
 */
class SetPropertyValueAction extends Action
{
    /**
     * @Groups({"WORKFLOW_ACTION"})
     *
     * @var string
     */
    protected $name = Action::SET_PROPERTY_VALUE_ACTION;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Property", inversedBy="setPropertyValueActions")
     */
    protected $property;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="array")
     */
    protected $joins = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $operator;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }
}
