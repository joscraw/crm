<?php

namespace App\EntityListener;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Model\DatePickerField;
use App\Model\FieldCatalog;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class CustomObjectListener
 * @package App\Entity\EntityListener
 */
class CustomObjectListener
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
     * CustomObjectListener constructor.
     * @param SerializerInterface $serializer
     * @param WorkflowRepository $workflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * Serialize the record properties before persisting
     *
     * @param CustomObject $customObject
     * @param LifecycleEventArgs $args
     */
    public function prePersist(CustomObject $customObject, LifecycleEventArgs $args)
    {
        $name = "josh";
        // todo possibly add the logic here for automations/workflows
    }

    /**
     * Deserialize the record properties after loading
     *
     * @param CustomObject $customObject
     * @param LifecycleEventArgs $args
     */
    public function postLoad(CustomObject $customObject, LifecycleEventArgs $args)
    {
        $name = "josh";
        // do nothing
    }

    /**
     * This gets called after a record is created for the first time
     *
     * @param CustomObject $customObject
     * @param LifecycleEventArgs $args
     */
    public function postPersist(CustomObject $customObject, LifecycleEventArgs $args) {

        $propertyGroup = new PropertyGroup();
        $propertyGroup->setInternalName('system_information');
        $propertyGroup->setName('System Information');
        $propertyGroup->setCustomObject($customObject);
        $propertyGroup->setSystemDefined(true);

        $createdAtProperty = new Property();
        $createdAtProperty->setInternalName('created_at');
        $createdAtProperty->setLabel('Created At');
        $createdAtProperty->setRequired(true);
        // todo possibly change to date time field once you create that field type
        $createdAtProperty->setFieldType(FieldCatalog::DATE_PICKER);
        $field = new DatePickerField();
        $createdAtProperty->setField($field);
        $createdAtProperty->setCustomObject($customObject);
        $createdAtProperty->setPropertyGroup($propertyGroup);
        $createdAtProperty->setSystemDefined(true);
        $createdAtProperty->setHidden(true);

        $updatedAtProperty = new Property();
        $updatedAtProperty->setInternalName('updated_at');
        $updatedAtProperty->setLabel('Updated At');
        $updatedAtProperty->setRequired(true);
        // todo possibly change to date time field once you create that field type
        $updatedAtProperty->setFieldType(FieldCatalog::DATE_PICKER);
        $field = new DatePickerField();
        $updatedAtProperty->setField($field);
        $updatedAtProperty->setCustomObject($customObject);
        $updatedAtProperty->setPropertyGroup($propertyGroup);
        $updatedAtProperty->setSystemDefined(true);
        $updatedAtProperty->setHidden(true);

        $idProperty = new Property();
        $idProperty->setInternalName('id');
        $idProperty->setLabel('ID');
        $idProperty->setRequired(true);
        $idProperty->setFieldType(FieldCatalog::SINGLE_LINE_TEXT);
        $field = new DatePickerField();
        $idProperty->setField($field);
        $idProperty->setCustomObject($customObject);
        $idProperty->setPropertyGroup($propertyGroup);
        $idProperty->setSystemDefined(true);
        $idProperty->setHidden(true);

        $this->entityManager->persist($propertyGroup);
        $this->entityManager->persist($createdAtProperty);
        $this->entityManager->persist($updatedAtProperty);
        $this->entityManager->persist($idProperty);
        $this->entityManager->flush();

        // todo possibly add the logic here for automations/workflows

    }

    /**
     * This gets called after a record is updated. This will only get called if the
     * data prior to the update is different from the data after the update
     *
     * @param CustomObject $customObject
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(CustomObject $customObject, LifecycleEventArgs $args) {

        $name = "josh";
        // todo possibly add the logic here for automations/workflows
        //  but take not this will only fire if the data is different at all. Will this work with JSON as well?
        //  since this is all in one column? Will this fire if one JSON value gets updated in that column? Hmmm. Test that out.
    }
}