<?php

namespace App\MessageHandler\Event;

use App\Entity\WorkflowInput;
use App\Message\Event\WorkflowCompletedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SaveWorkflowInput implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __invoke(WorkflowCompletedEvent $event)
    {
        $workflowInput = new WorkflowInput();
        $workflowInput->setInput($event->getLastMessageCompleted()->getInput());
        $workflowInput->setWorkflow($event->getWorkflow());
        $this->entityManager->persist($workflowInput);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

}