<?php

namespace App\Entity;

use App\Model\AbstractWorkflowTrigger;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\EntityListeners({"App\EntityListener\WorkflowTriggerListener"})
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowTriggerRepository")
 */
class WorkflowTrigger
{
    const PROPERTY_BASED_TRIGGER = 'PROPERTY_BASED_TRIGGER';

    const FORM_SUBMISSION_TRIGGER = 'FORM_SUBMISSION_TRIGGER';

    public static $availableTriggers = [
        'Property Based Trigger' => self::PROPERTY_BASED_TRIGGER,
        'Form Submission Trigger' => self::FORM_SUBMISSION_TRIGGER
    ];

    /**
     * @Groups({"WORKFLOW_TRIGGERS"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Workflow", inversedBy="workflowTriggers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    /**
     * @Groups({"WORKFLOW_TRIGGERS"})
     * @ORM\Column(type="string", length=255)
     */
    private $triggerType;

    /**
     *
     * @Groups({"WORKFLOW_TRIGGERS"})
     * @var AbstractWorkflowTrigger
     *
     * @ORM\Column(type="json", nullable=true, name="trigger_data")
     */
    private $trigger = [];

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

    public function getTriggerType(): ?string
    {
        return $this->triggerType;
    }

    public function setTriggerType(string $triggerType): self
    {
        $this->triggerType = $triggerType;

        return $this;
    }

    /**
     * @return AbstractWorkflowTrigger
     */
    public function getTrigger()
    {
        return $this->trigger;
    }

    /**
     * @param $trigger
     * @return WorkflowTrigger
     */
    public function setTrigger($trigger)
    {
        $this->trigger = $trigger;

        return $this;
    }
}
