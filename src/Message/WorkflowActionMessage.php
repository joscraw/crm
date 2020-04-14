<?php

namespace App\Message;

abstract class WorkflowActionMessage
{
    /**
     * @var int
     */
    protected $workflowActionId;

    /**
     * @var array
     */
    protected $input = [];

    /**
     * WorkflowActionMessage constructor.
     * @param int $workflowActionId
     */
    public function __construct(int $workflowActionId) {
        $this->workflowActionId = $workflowActionId;
    }

    /**
     * @return int
     */
    public function getActionId(): int {
        return $this->workflowActionId;
    }

    /**
     * @param int $id
     */
    public function setActionId(int $id) {
        $this->workflowActionId = $id;
    }

    /**
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * @param $input
     * @return WorkflowActionMessage
     */
    public function addInput(array $input): self
    {
        $this->input[] = $input;
        return $this;
    }
}