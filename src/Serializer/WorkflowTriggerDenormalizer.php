<?php

namespace App\Serializer;

use App\Entity\Property;
use App\Entity\Workflow;
use App\Model\AbstractField;
use App\Model\AbstractTrigger;
use App\Model\AbstractWorkflowTrigger;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\Filter;
use App\Model\MultiLineTextField;
use App\Model\MultipleCheckboxField;
use App\Model\NumberField;
use App\Model\PropertyBasedTrigger;
use App\Model\PropertyTrigger;
use App\Model\RadioSelectField;
use App\Model\SingleCheckboxField;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use App\Utils\PropertyHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class WorkflowTriggerDenormalizer
 * @package App\Serializer\
 */
class WorkflowTriggerDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{

    use PropertyHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;


    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * PropertyFieldDenormalizer constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * Sets the owning Denormalizer object.
     *
     * @param DenormalizerInterface $denormalizer
     */
    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }


    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param string $class The expected class to instantiate
     * @param string $format Format the given data was extracted from
     * @param array $context Options available to the denormalizer
     *
     * @return object
     *
     * @throws BadMethodCallException   Occurs when the normalizer is not called in an expected context
     * @throws InvalidArgumentException Occurs when the arguments are not coherent or not supported
     * @throws UnexpectedValueException Occurs when the item cannot be hydrated with the given data
     * @throws ExtraAttributesException Occurs when the item doesn't have attribute to receive given data
     * @throws LogicException           Occurs when the normalizer is not supposed to denormalize
     * @throws RuntimeException         Occurs if the class cannot be instantiated
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $trigger= null;
        switch($data['name']) {
            case AbstractTrigger::PROPERTY_BASED_TRIGGER:

                /** @var PropertyTrigger $trigger */
                $trigger = $this->denormalizer->denormalize(
                    $data,
                    PropertyTrigger::class,
                    $format,
                    $context
                );

                $filters = $this->setValidPropertyTypes($trigger->getFilters());

                $filtersArray = [];
                foreach($filters as $key => $filter) {

                    $filter = $this->denormalizer->denormalize(
                        $filter,
                        Filter::class,
                        $format,
                        $context
                    );

                    $filtersArray[] = $filter;
                }

                $trigger->setFilters($filtersArray);
                break;
        }
        return $trigger;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed $data Data to denormalize from
     * @param string $type The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if($type == AbstractTrigger::class) {
            return true;
        } else {
            return false;
        }
    }
}