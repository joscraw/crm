<?php

namespace App\Message;


class WorkflowPropertyUpdateActionMessage extends WorkflowActionMessage
{
    /**
     * @var int
     */
    private $recordId;

    /**
     * WorkflowActionMessage constructor.
     * @param $workflowActionId
     * @param $recordId
     */
    public function __construct($workflowActionId, $recordId) {
        $this->workflowActionId = $workflowActionId;
        $this->recordId = $recordId;

        parent::__construct($workflowActionId);
    }

    /**
     * @return int
     */
    public function getRecordId(): int
    {
        return $this->recordId;
    }

    /**
     * @param int $recordId
     */
    public function setRecordId(int $recordId): void
    {
        $this->recordId = $recordId;
    }
}