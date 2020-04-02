<?php

namespace App\EntityListener;

use App\Entity\Action;
use App\Entity\Property;
use App\Entity\PropertyTrigger;
use App\Entity\Record;
use App\Message\WorkflowMessage;
use App\Model\AbstractField;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param AdapterInterface $cache
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        AdapterInterface $cache
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
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
        $name = "josh";
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

        $record->created_at = $record->getCreatedAt()->format("m/d/Y");
        $record->updated_at = $record->getUpdatedAt()->format("m/d/Y");
        $record->id = $record->getId();
        $this->entityManager->persist($record);
        $this->entityManager->flush();

/*        if($this->cache->hasItem('contact_emails')) {
            $item = $this->cache->getItem('contact_emails');
            $contactEmails = $item->get();
            $contactEmails[] = $record->email;
            $item->set($contactEmails);
            $this->cache->save($item);
        } else {
            $contactEmails = [];
            $item = $this->cache->getItem('contact_emails');
            $contactEmails[] = $record->email;
            $item->set($contactEmails);
            $this->cache->save($item);
        }*/
        // todo possibly add the logic here for automations/workflows
    }

    /**
     * This gets called after a record is updated. This will only get called if the
     * data prior to the update is different from the data after the update
     *
     * @param Record $record
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Record $record, LifecycleEventArgs $args) {

        $record->updated_at = $record->getUpdatedAt()->format("m/d/Y");
        $this->entityManager->persist($record);
        $this->entityManager->flush();
        // todo possibly add the logic here for automations/workflows
        //  but take not this will only fire if the data is different at all. Will this work with JSON as well?
        //  since this is all in one column? Will this fire if one JSON value gets updated in that column? Hmmm. Test that out.
    }
}