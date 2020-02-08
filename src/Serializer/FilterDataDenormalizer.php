<?php

namespace App\Serializer;

use App\Model\Filter\Column;
use App\Model\Filter\Filter;
use App\Entity\Property;
use App\Model\AbstractField;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\Filter\FilterData;
use App\Model\Filter\Join;
use App\Model\MultiLineTextField;
use App\Model\MultipleCheckboxField;
use App\Model\NumberField;
use App\Model\RadioSelectField;
use App\Model\SingleCheckboxField;
use App\Model\SingleLineTextField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
class FilterDataDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
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
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $filterData = new FilterData();

        // BASE OBJECT
        $baseObject = $this->customObjectRepository->find($data['baseObject']);
        $filterData->setBaseObject($baseObject);

        // COLUMNS TO RETURN
        foreach($data['columns'] as $columnData) {
            $column = new Column();
            $property = $this->propertyRepository->find($columnData['property']);
            $column->setProperty($property);
            $filterData->addColumn($column);
        }

        // OR FILTERS
        foreach($data['orFilters'] as $orFilter) {
            $property = $this->propertyRepository->find($orFilter['property']);
            /** @var Filter $filter */
            $filter = $this->denormalizer->denormalize(
                $orFilter,
                Filter::class,
                $format,
                $context
            );
            $filter->setProperty($property);
            $filterData->addOrFilter($filter);
        }

        // JOINS
        $filterData->setJoins($this->joins($data['joins']));

        return $filterData;
    }

    private function joins($joins) {

        $joinCollection = new ArrayCollection();
        /** @var Join $join */
        foreach($joins as $join) {

            $joinObject = new Join();
            $relationshipPropertyToJoinOn = $this->propertyRepository->find($join['relationshipPropertyToJoinOn']);
            $joinObject->setRelationshipPropertyToJoinOn($relationshipPropertyToJoinOn);
            $joinObject->setJoinType($join['joinType']);
            $joinObject->setJoinDirection($join['joinDirection']);

            foreach($join['columns'] as $columnData) {
                $column = new Column();
                $property = $this->propertyRepository->find($columnData['property']);
                $column->setProperty($property);
                $joinObject->addColumn($column);
            }

            // OR FILTERS
            foreach($join['orFilters'] as $orFilter) {
                $property = $this->propertyRepository->find($orFilter['property']);
                /** @var Filter $filter */
                $filter = $this->denormalizer->denormalize(
                    $orFilter,
                    Filter::class,
                    'json',
                   []
                );
                $filter->setProperty($property);
                $joinObject->addOrFilter($filter);
            }

            if(count($join['joins']) > 0) {
                $joinObject->setJoins($this->joins($join['joins']));
            }

            $joinCollection->add($joinObject);
        }
        return $joinCollection;
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
        if($type == FilterData::class) {
            return true;
        } else {
            return false;
        }
    }
}