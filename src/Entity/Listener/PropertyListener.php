<?php

namespace App\Entity\Listener;

use App\Entity\Property;
use App\Model\AbstractField;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
     * @param Property $property
     */
    private function serializePropertyField(Property $property)
    {
        $propertyField = $property->getField();
        $data = $this->serializer->serialize($propertyField, 'json');
        $property->setField(json_decode($data, true));
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Property $property
     */
    private function deserializePropertyField(Property $property)
    {
        /*$encoders = array(new XmlEncoder(), new JsonEncoder());
         $normalizers = array(new ObjectNormalizer());
         $serializer = new Serializer($normalizers, $encoders);

        $propertyField = json_encode($property->getField()['selectizeSearchResultProperties'][0]);

        $propertyField = $this->serializer->deserialize($propertyField, Property::class, 'json');

        */

        $propertyField = json_encode($property->getField());
        $propertyField = $this->serializer->deserialize($propertyField, AbstractField::class, 'json');
        $property->setField($propertyField);
    }


    /**
     *  Set the first 5 properties to display on the columns for the datatables
     * that way the datatables is not empty
     * @param Property $property
     */
    private function setWhetherOrNotIsColumn(Property $property) {

        $columnCount = $this->entityManager->getRepository(Property::class)->getCountWherePropertyIsColumn($property->getCustomObject());
        $columnCount = (int) $columnCount[0]['count'];

        $highestColumnOrder = $this->entityManager->getRepository(Property::class)->getHighestColumnOrder($property->getCustomObject());
        if($columnCount <= 5) {
            $property->setIsColumn(true);

            if($highestColumnOrder[0]['column_order'] === null) {
                $property->setColumnOrder(0);
            } else {
                $highestColumn = (int) $highestColumnOrder[0]['column_order'];
                $property->setColumnOrder(++$highestColumn);
            }
        }
    }

    /**
     * Set the first 5 properties to be default properties to display in the create record modal
     * that way the modal is not empty
     * @param Property $property
     */
    private function setWhetherOrNotIsDefaultProperty(Property $property) {

        $defaultPropertyCount = $this->entityManager->getRepository(Property::class)->getCountWherePropertyIsDefaultProperty($property->getCustomObject());
        $defaultPropertyCount = (int) $defaultPropertyCount[0]['count'];

        $highestDefaultPropertyOrder = $this->entityManager->getRepository(Property::class)->getHighestDefaultPropertyOrder($property->getCustomObject());
        if($defaultPropertyCount <= 5) {
            $property->setIsDefaultProperty(true);

            if($highestDefaultPropertyOrder[0]['default_property_order'] === null) {
                $property->setDefaultPropertyOrder(0);
            } else {
                $highestProperty = (int) $highestDefaultPropertyOrder[0]['default_property_order'];
                $property->setDefaultPropertyOrder(++$highestProperty);
            }
        }
    }

    private function setColumnOrder(Property $property) {

        $properties = $this->entityManager->getRepository(Property::class)->findBy([
            'isColumn' => true
        ]);

        if(count($properties) <= 5) {
            $property->setIsColumn(true);
        }

    }

    private function setDefaultPropertyOrder(Property $property) {

        $properties = $this->entityManager->getRepository(Property::class)->findBy([
            'isDefaultProperty' => true
        ]);

        if(count($properties) <= 5) {
            $property->setIsDefaultProperty(true);
        }

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
        $this->setWhetherOrNotIsColumn($property);
        /*$this->setColumnOrder($property);*/
        $this->setWhetherOrNotIsDefaultProperty($property);
        /*$this->setDefaultPropertyOrder($property);*/
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

    /**
     * Serialize the content again if it gets updated
     *
     * @param Property $property
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(Property $property, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($property);
    }
}