<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActionRepository")
 * @Table(name="workflow_action")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"setPropertyValueAction" = "SetPropertyValueAction"})
 *
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "set_property_value_action"="App\Entity\SetPropertyValueAction"
 * })
 */
abstract class Action
{
    const SET_PROPERTY_VALUE_ACTION = 'set_property_value_action';

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @ORM\Column(type="string", length=255)
     */
    protected $uid;

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @Groups({"WORKFLOW_ACTION"})
     * @ORM\Column(type="string", length=255)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Workflow", inversedBy="actions")
     */
    private $workflow;

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }
}
