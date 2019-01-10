<?php

namespace App\EntityListener;

use App\Entity\Property;
use App\Entity\Record;
use App\Model\AbstractField;
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

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serialize the field properties of the Property entity
     *
     * @param Record $record
     */
    private function serializePropertiesField(Record $record)
    {
        $properties = $record->getProperties();
        $record->setProperties($this->serializer->serialize($properties, 'json'));
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Record $record
     */
    private function deserializePropertiesField(Record $record)
    {
        $properties = $record->getProperties();
        $properties = $this->serializer->deserialize($properties, 'array', 'json');

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
}