<?php

namespace App\MessageHandler;

use App\Entity\Workflow;
use App\Message\Event\WorkflowCompletedEvent;
use App\Message\WorkflowActionMessage;
use App\Repository\RecordRepository;
use App\Repository\WorkflowActionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class WorkflowActionHandler implements MessageHandlerInterface
{
    /**
     * @var RecordRepository
     */
    protected $recordRepository;

    /**
     * @var WorkflowActionRepository
     */
    protected $workflowActionRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var MessageBusInterface $bus
     */
    protected $bus;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * WorkflowPropertyUpdateActionMessageHandler constructor.
     * @param RecordRepository $recordRepository
     * @param WorkflowActionRepository $workflowActionRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     */
    public function __construct(
        RecordRepository $recordRepository,
        WorkflowActionRepository $workflowActionRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        SerializerInterface $serializer
    ) {
        $this->recordRepository = $recordRepository;
        $this->workflowActionRepository = $workflowActionRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->serializer = $serializer;
    }

    /**
     * @param Workflow $workflow
     * @param WorkflowActionMessage $workflowActionMessage
     */
    protected function complete(Workflow $workflow, WorkflowActionMessage $workflowActionMessage) {

        $this->bus->dispatch(new WorkflowCompletedEvent($workflowActionMessage, $workflow));
    }
}