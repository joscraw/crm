<?php

namespace App\MessageHandler;

use App\Entity\WorkflowAction;
use App\Entity\WorkflowPropertyUpdateAction;
use App\Message\WorkflowActionMessage;
use App\Message\WorkflowPropertyUpdateActionMessage;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class WorkflowPropertyUpdateActionMessageHandler extends WorkflowActionHandler
{

    public function __invoke(WorkflowPropertyUpdateActionMessage $message) {

        /** @var WorkflowPropertyUpdateAction $workflowAction */
        $workflowAction = $this->workflowActionRepository->find($message->getActionId());
        $record = $this->recordRepository->find($message->getRecordId());

        if (!$workflowAction || !$record) {
            throw new UnrecoverableMessageHandlingException("Workflow action or record not found.");
        }

        $property = $workflowAction->getProperty();
        $internalName = $property->getInternalName();
        $record->$internalName = $workflowAction->getValue();

        $this->entityManager->flush();

        $workflow = $workflowAction->getWorkflow();
        /** @var WorkflowAction $nextWorkflowAction */
        $nextWorkflowAction = $workflow->getNextActionInSequence($workflowAction);

        if(!$nextWorkflowAction) {
            $this->complete($workflowAction->getWorkflow(), $message);
            return;
        }

        // Store a reference to the previous message so we can pass it in as input to the next
        $previousMessage = $message;

        /** @var WorkflowActionMessage $message */
        $message = $nextWorkflowAction->getHandlerMessage();

        // todo this is duplicate code here. I think we are going to need a factory class or something
        switch ($message) {
            case WorkflowPropertyUpdateActionMessage::class:
                $message = new $message($nextWorkflowAction->getId(), $record->getId());
                break;
        }

        $input = [
            'recordId' => $record->getId(),
            'recordProperties' => $record->getProperties()
        ];

        $message->addInput($input)
            ->addInput($previousMessage->getInput());

        $this->bus->dispatch($message);
    }
}