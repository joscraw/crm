<?php

namespace App\EntityListener;

use App\Entity\Property;
use App\Model\AbstractField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class PropertyListener
 * @package App\Entity\EntityListener
 */
class PropertyListener
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
     * Serialize the field property of the Property entity
     *
     * @param Property $property
     */
    private function serializePropertyField(Property $property)
    {
        $propertyField = $property->getField();
        $property->setField($this->serializer->serialize($propertyField, 'json'));
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Property $property
     */
    private function deserializePropertyField(Property $property)
    {
        $propertyField = $property->getField();
        $propertyField = $this->serializer->deserialize($propertyField, AbstractField::class, 'json');

        $property->setField($propertyField);
    }

    /**
     * Serialize the content property before persisting
     *
     * @param Property $property
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Property $property, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($property);
    }

    /**
     * Deserialize the content property after loading
     *
     * @param Property $property
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Property $property, LifecycleEventArgs $args)
    {
        $this->deserializePropertyField($property);
    }
}