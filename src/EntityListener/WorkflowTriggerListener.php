<?php

namespace App\EntityListener;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\WorkflowTrigger;
use App\Model\AbstractField;
use App\Model\CustomObjectField;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;



use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class WorkflowTriggerListener
 * @package App\Entity\EntityListener
 */
class WorkflowTriggerListener
{

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(SerializerInterface $serializer,  EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Serialize the field property of the Property entity
     *
     * @param WorkflowTrigger $workflowTrigger
     */
    private function serializePropertyField(WorkflowTrigger $workflowTrigger)
    {
        $trigger = $workflowTrigger->getTrigger();
        $data = $this->serializer->serialize($trigger, 'json', ['groups' => ['WORKFLOW_TRIGGER_DATA']]);
        $workflowTrigger->setTrigger(json_decode($data, true));
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param WorkflowTrigger $workflowTrigger
     */
    private function deserializePropertyField(WorkflowTrigger $workflowTrigger)
    {
        /*$encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

        $propertyField = json_encode($property->getField()['selectizeSearchResultProperties'][0]);

        $propertyField = $this->serializer->deserialize($propertyField, Property::class, 'json');

        $name = "Josh";*/


      /*  $j = "Hi";

        $name = "JosH";
        $propertyField = json_encode($property->getField());
        $propertyField = $this->serializer->deserialize($propertyField, AbstractField::class, 'json');
        $property->setField($propertyField);*/
    }

    /**
     * Serialize the content property before persisting
     *
     * @param WorkflowTrigger $workflowTrigger
     * @param LifecycleEventArgs $args
     */
    public function prePersist(WorkflowTrigger $workflowTrigger, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($workflowTrigger);

    }

    /**
     * Deserialize the content property after loading
     *
     * @param WorkflowTrigger $workflowTrigger
     * @param LifecycleEventArgs $args
     */
    public function postLoad(WorkflowTrigger $workflowTrigger, LifecycleEventArgs $args)
    {
        $this->deserializePropertyField($workflowTrigger);
    }

    /**
     * Serialize the content again if it gets updated
     *
     * @param WorkflowTrigger $workflowTrigger
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(WorkflowTrigger $workflowTrigger, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($workflowTrigger);
    }
}