<?php

namespace App\EntityListener;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Model\AbstractField;
use App\Model\CustomObjectField;
use App\Repository\PropertyRepository;
use App\Serializer\FieldNormalizer;
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
        /*$propertyField = $property->getField();
        $property->setField($this->serializer->serialize($propertyField, 'json'));*/
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

        $name = "Josh";*/

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

        $properties = $this->entityManager->getRepository(Property::class)->findBy([
           'isColumn' => true
        ]);

        $highestColumnOrder = $this->entityManager->getRepository(Property::class)->getHighestColumnOrder($property->getCustomObject());
        if(count($properties) <= 5) {
            $property->setIsColumn(true);

            if($highestColumnOrder[0]['column_order'] === null) {
                $property->setColumnOrder(0);
            } else {
                $highestColumn = (int) $highestColumnOrder[0]['column_order'];
                $property->setColumnOrder(++$highestColumn);
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
        $this->setColumnOrder($property);
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