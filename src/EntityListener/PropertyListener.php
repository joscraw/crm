<?php

namespace App\EntityListener;

use App\Entity\Property;
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
     * Serialize the content property before persisting
     *
     * @param Property $property
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Property $property, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($property);
    }
}