<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowLogRepository")
 */
class WorkflowLog
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Workflow", inversedBy="workflowLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkflowAction", inversedBy="workflowLogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $action;

    /**
     * @ORM\Column(type="json")
     */
    private $input = [];

    /**
     * @ORM\Column(type="json")
     */
    private $output = [];

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

    public function getAction(): ?WorkflowAction
    {
        return $this->action;
    }

    public function setAction(?WorkflowAction $action): self
    {
        $this->action = $action;

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

    public function getOutput(): ?array
    {
        return $this->output;
    }

    public function setOutput(array $output): self
    {
        $this->output = $output;

        return $this;
    }
}
