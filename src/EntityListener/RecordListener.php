<?php

namespace App\EntityListener;

use App\Entity\Action;
use App\Entity\Property;
use App\Entity\PropertyTrigger;
use App\Entity\Record;
use App\Model\AbstractField;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
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
     * @var ObjectWorkflowRepository
     */
    private $objectWorkflowRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RecordListener constructor.
     * @param SerializerInterface $serializer
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Serialize the field properties of the Property entity
     *
     * @param Record $record
     */
    private function serializePropertiesField(Record $record) {}

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Record $record
     */
    private function deserializePropertiesField(Record $record)
    {
        $properties = json_encode($record->getProperties());
        // we aren't mapping the properties to a specific object
        $properties = json_decode($properties, true);

        $record->setProperties($properties);
    }

    /**
     * Serialize the record properties before persisting
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Record $record, LifecycleEventArgs $args)
    {
        $this->serializePropertiesField($record);
    }

    /**
     * Deserialize the record properties after loading
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Record $record, LifecycleEventArgs $args)
    {
        $this->deserializePropertiesField($record);
    }

    /**
     * This gets called after a record is created for the first time
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Record $record, LifecycleEventArgs $args) {}

    /**
     * This gets called after a record is updated. This will only get called if the
     * data prior to the update is different from the data after the update
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\DBAL\DBALException
     */
    public function postUpdate(Record $record, LifecycleEventArgs $args) {}
}