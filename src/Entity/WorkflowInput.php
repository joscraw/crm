<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowInputRepository")
 */
class WorkflowInput
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Workflow", inversedBy="workflowInputs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    /**
     * @ORM\Column(type="json")
     */
    private $input = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInput(): ?array
    {
        return $this->input;
    }

    public function setInput(array $input): self
    {
        $this->input = $input;

        return $this;
    }
}
