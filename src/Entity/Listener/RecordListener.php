<?php

namespace App\Entity\Listener;

use App\Entity\Record;
use App\Entity\Workflow;
use App\Entity\WorkflowAction;
use App\Message\WorkflowActionMessage;
use App\Model\WorkflowTrigger;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class RecordListener
 * @package App\Entity\EntityListener
 */
class RecordListener
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var AdapterInterface $cache
     */
    private $cache;

    /**
     * RecordListener constructor.
     * @param SerializerInterface $serializer
     * @param WorkflowRepository $workflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param AdapterInterface $cache
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        AdapterInterface $cache
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->cache = $cache;
    }


    /**
     * Serialize the record properties before persisting
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Record $record, LifecycleEventArgs $args)
    {
        // todo possibly add the logic here for automations/workflows
    }

    /**
     * Deserialize the record properties after loading
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Record $record, LifecycleEventArgs $args)
    {
        // do nothing
    }

    /**
     * This gets called after a record is created for the first time
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function postPersist(Record $record, LifecycleEventArgs $args) {

        // todo I'm thinking we actually need to add an Event Listener that's not
        //  tied to the actual class like this. Instead of an entity listener
        //  and event listener would allow us to fire the listener
        //  from inside a Message Handler as well if you want properties that are updated
        //  from inside a message handler to actually be able to fire other workflows.
        //  Which I'm pretty sure we do right? Should we keep track of records that
        //  have already been enrolled into an automation.

        $this->createSystemDefinedProperties($record, $args);
    }

    /**
     * This gets called after a record is updated. This will only get called if the
     * data prior to the update is different from the data after the update
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Record $record, LifecycleEventArgs $args) {

        $this->updateSystemDefinedProperties($record, $args);
    }

    private function createSystemDefinedProperties(Record $record, LifecycleEventArgs $args) {

        $record->created_at = $record->getCreatedAt()->format("m/d/Y");
        $record->updated_at = $record->getUpdatedAt()->format("m/d/Y");
        $record->id = $record->getId();
        $this->entityManager->flush();

        return $this;
    }

    private function updateSystemDefinedProperties(Record $record, LifecycleEventArgs $args) {

        $record->updated_at = $record->getUpdatedAt()->format("m/d/Y");
        $this->entityManager->flush();

        return $this;
    }
}