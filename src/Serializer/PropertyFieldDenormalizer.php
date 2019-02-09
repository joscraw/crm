<?php

namespace App\Serializer;

use App\Entity\Property;
use App\Model\AbstractField;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\MultiLineTextField;
use App\Model\MultipleCheckboxField;
use App\Model\NumberField;
use App\Model\RadioSelectField;
use App\Model\SingleCheckboxField;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
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
 * Class PropertyFieldDenormalizer
 * @package App\Serializer\
 */
class PropertyFieldDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
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

        $field = null;
        switch($data['name']) {
            case FieldCatalog::CUSTOM_OBJECT:
                $field = $this->denormalizer->denormalize(
                    $data,
                    CustomObjectField::class,
                    $format,
                    $context
                );
                $customObject = $this->customObjectRepository->find($field->getCustomObject()->getId());
                $field->setCustomObject($customObject);
                break;
            case FieldCatalog::SINGLE_LINE_TEXT:
                $field = $this->denormalizer->denormalize(
                    $data,
                    SingleLineTextField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::MULTI_LINE_TEXT:
                $field = $this->denormalizer->denormalize(
                    $data,
                    MultiLineTextField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::DROPDOWN_SELECT:
                $field = $this->denormalizer->denormalize(
                    $data,
                    DropdownSelectField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::SINGLE_CHECKBOX:
                $field = $this->denormalizer->denormalize(
                    $data,
                    SingleCheckboxField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::MULTIPLE_CHECKBOX:
                $field = $this->denormalizer->denormalize(
                    $data,
                    MultipleCheckboxField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::RADIO_SELECT:
                $field = $this->denormalizer->denormalize(
                    $data,
                    RadioSelectField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::NUMBER:
                $field = $this->denormalizer->denormalize(
                    $data,
                    NumberField::class,
                    $format,
                    $context
                );
                break;
            case FieldCatalog::DATE_PICKER:
                $field = $this->denormalizer->denormalize(
                    $data,
                    DatePickerField::class,
                    $format,
                    $context
                );
                break;
        }

        return $field;
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
        if($type == AbstractField::class) {
            return true;
        } else {
            return false;
        }
    }
}