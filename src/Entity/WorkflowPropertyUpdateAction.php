<?php

namespace App\Entity;

use App\Message\WorkflowPropertyUpdateActionMessage;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowPropertyUpdateActionRepository")
 */
class WorkflowPropertyUpdateAction extends WorkflowAction
{
    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    protected static $name = WorkflowAction::WORKFLOW_PROPERTY_UPDATE_ACTION;

    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    protected static $description = 'Workflow action for property update.';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Property", inversedBy="workflowPropertyUpdateActions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $property;

    public function getHandlerMessage() {

        return WorkflowPropertyUpdateActionMessage::class;
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

    public function getProperty(): ?Property
    {
        return $this->property;
    }

    public function setProperty(?Property $property): self
    {
        $this->property = $property;

        return $this;
    }
}
