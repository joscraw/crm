<?php

namespace App\Message\Event;

use App\Entity\Workflow;
use App\Message\WorkflowActionMessage;

class WorkflowCompletedEvent
{
    /**
     * @var WorkflowActionMessage
     */
    private $lastMessageCompleted;

    /**
     * @var Workflow
     */
    private $workflow;

    /**
     * WorkflowCompletedEvent constructor.
     * @param WorkflowActionMessage $lastMessageCompleted
     * @param Workflow $workflow
     */
    public function __construct(WorkflowActionMessage $lastMessageCompleted, Workflow $workflow)
    {
        $this->lastMessageCompleted = $lastMessageCompleted;
        $this->workflow = $workflow;
    }

    /**
     * @return WorkflowActionMessage
     */
    public function getLastMessageCompleted(): WorkflowActionMessage
    {
        return $this->lastMessageCompleted;
    }

    /**
     * @return Workflow
     */
    public function getWorkflow(): Workflow
    {
        return $this->workflow;
    }

}