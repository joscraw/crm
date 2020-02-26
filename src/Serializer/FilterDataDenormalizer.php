<?php

namespace App\Serializer;

use App\Api\ApiProblemException;
use App\Model\Filter\AbstractCriteria;
use App\Model\Filter\AndCriteria;
use App\Model\Filter\Column;
use App\Model\Filter\Filter;
use App\Entity\Property;
use App\Model\AbstractField;
use App\Model\CustomObjectField;
use App\Model\DatePickerField;
use App\Model\DropdownSelectField;
use App\Model\FieldCatalog;
use App\Model\Filter\FilterCriteria;
use App\Model\Filter\FilterData;
use App\Model\Filter\Join;
use App\Model\Filter\OrCriteria;
use App\Model\Filter\Order;
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
     * @var string
     */
    public $statement;

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

        // SEARCH
        if(isset($data['search'])) {
            $filterData->setSearch($data['search']);
        }

        // LIMIT
        if(isset($data['limit'])) {
            $filterData->setLimit($data['limit']);
        }

        // OFFSET
        if(isset($data['offset'])) {
            $filterData->setOffset($data['offset']);
        }

        // STATEMENT (SELECT, UPDATE, ETC)
        if(isset($data['statement'])) {
            if(!in_array($data['statement'], $filterData->supportedStatements)) {
                throw new ApiProblemException(400, sprintf('Statement %s not supported. Supported statements are: %s',
                    $data['statement'],
                    implode(",", $filterData->supportedStatements)
                ));
            }
            $this->statement = $data['statement'];
            $filterData->setStatement($data['statement']);
        }

        if(isset($data['filterCriteria'])) {
            /** @var FilterCriteria $filterCriteria */
            $filterCriteria = $this->filterCriteria($data['filterCriteria'], new FilterCriteria());
            $filterData->setFilterCriteria($filterCriteria);
        }

        // COLUMNS TO RETURN
        if(isset($data['columns'])) {
            foreach($data['columns'] as $columnData) {
                if(!isset($columnData['property'])) {
                    throw new ApiProblemException(400, 'Each column object must have a property. Example: "property": 1');
                }
                $column = new Column();
                $property = $this->propertyRepository->find($columnData['property']);
                if(!$property) {
                    throw new ApiProblemException(400, sprintf("property: %s not not found", $columnData['property']));
                }
                if(isset($columnData['renameTo'])) {
                    $column->setRenameTo($columnData['renameTo']);
                }
                if(isset($columnData['newValue'])) {
                    $column->setNewValue($columnData['newValue']);
                }
                if($this->statement === 'UPDATE' && !isset($columnData['newValue'])) {
                    throw new ApiProblemException(400, sprintf('All columns must have a newValue property when using the UPDATE Statement. Example: newValue: "super cool new value here"'));
                }
                $column->setProperty($property);
                $filterData->addColumn($column);
            }
        }

        // FILTERS
        if(isset($data['filters'])) {
            foreach($data['filters'] as $filter) {
                if(!isset($filter['property'])) {
                    throw new ApiProblemException(400, 'Each filter object must have a property. Example: "property": 1');
                }
                $property = $this->propertyRepository->find($filter['property']);
                if(!$property) {
                    throw new ApiProblemException(400, sprintf("property: %s not not found", $filter['property']));
                }
                /** @var Filter $filterObject */
                $filterObject = $this->denormalizer->denormalize(
                    $filter,
                    Filter::class,
                    $format,
                    $context
                );
                $filterObject->setProperty($property);
                $filterData->addFilter($filterObject);
            }
        }

        if(isset($data['order'])) {
            foreach($data['order'] as $order) {
                if(!isset($order['property'])) {
                    throw new ApiProblemException(400, 'Each order object must have a property. Example: "property": 1');
                }
                $property = $this->propertyRepository->find($order['property']);
                if(!$property) {
                    throw new ApiProblemException(400, sprintf("property: %s not not found", $order['property']));
                }
                /** @var Order $orderObject */
                $orderObject = $this->denormalizer->denormalize(
                    $order,
                    Order::class,
                    $format,
                    $context
                );
                $orderObject->setProperty($property);
                $filterData->addOrder($orderObject);
            }
        }

        // JOINS
        if(isset($data['joins'])) {
            $filterData->setJoins($this->joins($data['joins']));
        }

        return $filterData;
    }

    /**
     * @param $data
     * @param AbstractCriteria $filterCriteria
     * @return AbstractCriteria
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function filterCriteria($data, AbstractCriteria $filterCriteria) {

        $andCollection = new ArrayCollection();
        $orCollection = new ArrayCollection();

        if(isset($data['and'])) {
            foreach($data['and'] as $and) {
                if(!isset($and['uid'])) {
                    throw new ApiProblemException(400, 'Each and criteria must have a uid. Example: "uid": 1');
                }
                $andCriteriaObject = new AndCriteria();
                $andCriteriaObject->setUid($and['uid']);
                $andCollection->add($andCriteriaObject);
                $this->filterCriteria($and, $andCriteriaObject);
            }
        }

        if(isset($data['or'])) {
            foreach($data['or'] as $or) {
                if(!isset($or['uid'])) {
                    throw new ApiProblemException(400, 'Each or criteria must have a uid. Example: "uid": 1');
                }
                $orCriteriaObject = new OrCriteria();
                $orCriteriaObject->setUid($or['uid']);
                $orCollection->add($orCriteriaObject);
                $this->filterCriteria($or, $orCriteriaObject);
            }
        }

        $filterCriteria->setAndCriteria($andCollection);
        $filterCriteria->setOrCriteria($orCollection);

        return $filterCriteria;
    }

    private function joins($joins) {

        $joinCollection = new ArrayCollection();
        /** @var Join $join */
        foreach($joins as $join) {

            if(!isset($join['relationshipPropertyToJoinOn'])) {
                throw new ApiProblemException(400, 'Each join object must have a relationshipPropertyToJoinOn. Example: "relationshipPropertyToJoinOn": 11');
            }

            if(!isset($join['joinType'])) {
                throw new ApiProblemException(400, 'Each join object must have a joinType. Example: "joinType": "With"');
            }

            $joinObject = new Join();
            $relationshipPropertyToJoinOn = $this->propertyRepository->find($join['relationshipPropertyToJoinOn']);

            if(!$relationshipPropertyToJoinOn) {
                throw new ApiProblemException(400, sprintf("relationshipPropertyToJoinOn: %s not not found", $join['relationshipPropertyToJoinOn']));
            }

            $joinObject->setRelationshipPropertyToJoinOn($relationshipPropertyToJoinOn);
            $joinObject->setJoinType($join['joinType']);

            // COLUMNS
            if(isset($join['columns'])) {
                foreach($join['columns'] as $columnData) {
                    if(!isset($columnData['property'])) {
                        throw new ApiProblemException(400, 'Each column object must have a property. Example: "property": 1');
                    }
                    $column = new Column();
                    $property = $this->propertyRepository->find($columnData['property']);
                    if(!$property) {
                        throw new ApiProblemException(400, sprintf("property: %s not not found", $columnData['property']));
                    }
                    if(isset($columnData['renameTo'])) {
                        $column->setRenameTo($columnData['renameTo']);
                    }
                    if(isset($columnData['newValue'])) {
                        $column->setNewValue($columnData['newValue']);
                    }
                    if($this->statement === 'UPDATE' && !isset($columnData['newValue'])) {
                        throw new ApiProblemException(400, sprintf('All columns must have a newValue property when using the UPDATE Statement. Example: newValue: "super cool new value here"'));
                    }
                    $column->setProperty($property);
                    $joinObject->addColumn($column);
                }
            }

            // OR FILTERS
            if(isset($join['filters'])) {
                foreach($join['filters'] as $filter) {
                    if(!isset($filter['property'])) {
                        throw new ApiProblemException(400, 'Each filter object must have a property. Example: "property": 1');
                    }
                    $property = $this->propertyRepository->find($filter['property']);
                    if(!$property) {
                        throw new ApiProblemException(400, sprintf("property: %s not not found", $filter['property']));
                    }
                    /** @var Filter $filterObject */
                    $filterObject = $this->denormalizer->denormalize(
                        $filter,
                        Filter::class,
                        'json',
                        []
                    );
                    $filterObject->setProperty($property);
                    $joinObject->addFilter($filterObject);
                }
            }

            if(isset($join['order'])) {
                foreach($join['order'] as $order) {
                    if(!isset($order['property'])) {
                        throw new ApiProblemException(400, 'Each order object must have a property. Example: "property": 1');
                    }
                    $property = $this->propertyRepository->find($order['property']);
                    if(!$property) {
                        throw new ApiProblemException(400, sprintf("property: %s not not found", $order['property']));
                    }
                    /** @var Order $orderObject */
                    $orderObject = $this->denormalizer->denormalize(
                        $order,
                        Order::class,
                        'json',
                        []
                    );
                    $orderObject->setProperty($property);
                    $joinObject->addOrder($orderObject);
                }
            }

            if(isset($join['joins']) && count($join['joins']) > 0) {
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