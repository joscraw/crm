<?php

namespace App\Serializer;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Model\Filter\AbstractCriteria;
use App\Model\Filter\AndCriteria;
use App\Model\Filter\Column;
use App\Model\Filter\Filter;
use App\Model\Filter\FilterCriteria;
use App\Model\Filter\FilterData;
use App\Model\Filter\GroupBy;
use App\Model\Filter\Join;
use App\Model\Filter\OrCriteria;
use App\Model\Filter\Order;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

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
            if(!in_array($data['statement'], FilterData::SUPPORTED_STATEMENTS)) {

                throw new ApiException(new ApiErrorResponse(
                    sprintf('Statement %s not supported. Supported statements are: %s',
                        $data['statement'],
                        implode(",", FilterData::SUPPORTED_STATEMENTS)
                    ),
                    ApiErrorResponse::TYPE_QUERY_ERROR,
                    [],
                    Response::HTTP_BAD_REQUEST
                ));
            }
            $this->statement = $data['statement'];
            $filterData->setStatement($data['statement']);
        }

        // COUNT ONLY
        if(isset($data['countOnly'])) {
            if(!in_array($data['countOnly'], [true, false])) {
                throw new ApiException(new ApiErrorResponse(
                    'countOnly property can only be true or false. Example: "countOnly": true',
                    ApiErrorResponse::TYPE_QUERY_ERROR,
                    [],
                    Response::HTTP_BAD_REQUEST
                ));
            }
            $filterData->setCountOnly($data['countOnly']);
        }

        // FILTER CRITERIA
        if(isset($data['filterCriteria'])) {
            /** @var FilterCriteria $filterCriteria */
            $filterCriteria = $this->filterCriteria($data['filterCriteria'], $filterData->getFilterCriteria());
            $filterData->setFilterCriteria($filterCriteria);
        }

        // COLUMNS TO RETURN
        if(isset($data['columns'])) {
            foreach($data['columns'] as $columnData) {
                if(!isset($columnData['property'])) {
                    throw new ApiException(new ApiErrorResponse(
                        'Each column object must have a property. Example: "property": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }
                $column = new Column();
                $property = $this->propertyRepository->find($columnData['property']);
                if(!$property) {
                    throw new ApiException(new ApiErrorResponse(
                        sprintf("property: %s not not found", $columnData['property']),
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }
                if(isset($columnData['renameTo'])) {
                    $column->setRenameTo($columnData['renameTo']);
                }
                if(isset($columnData['newValue'])) {
                    $column->setNewValue($columnData['newValue']);
                }
                if(isset($columnData['uid'])) {
                    $column->setUid($columnData['uid']);
                }
                if($this->statement === 'UPDATE' && !isset($columnData['newValue'])) {
                    throw new ApiException(new ApiErrorResponse(
                        sprintf('All columns must have a newValue property when using the UPDATE Statement. Example: newValue: "super cool new value here"'),
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }
                $column->setProperty($property);
                $filterData->addColumn($column);
            }
        }

        // FILTERS
        if(isset($data['filters'])) {
            foreach($data['filters'] as $filter) {

                if(!isset($filter['uid'])) {
                    throw new ApiException(new ApiErrorResponse(
                        'Each filter object must have a uid. Example: "uid": "1"',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }

                if(!isset($filter['property'])) {
                    throw new ApiException(new ApiErrorResponse(
                        'Each filter object must have a property. Example: "property": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }
                $property = $this->propertyRepository->find($filter['property']);
                if(!$property) {
                    throw new ApiException(new ApiErrorResponse(
                        sprintf("property: %s not not found", $filter['property']),
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
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

        // ORDER
        if(isset($data['orders'])) {
            foreach($data['orders'] as $order) {
                if(!isset($order['uid'])) {
                    throw new ApiException(new ApiErrorResponse(
                        'Each order object must have a uid. Example: "uid": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }

                /** @var Order $orderObject */
                $orderObject = $this->denormalizer->denormalize(
                    $order,
                    Order::class,
                    $format,
                    $context
                );

                $filterData->addOrder($orderObject);
            }
        }

        // GROUP BY
        if(isset($data['groupBys'])) {
            foreach($data['groupBys'] as $groupBy) {
                if(!isset($groupBy['uid'])) {
                    throw new ApiException(new ApiErrorResponse(
                        'Each group by object must have a uid. Example: "uid": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
                }

                /** @var GroupBy $groupByObject */
                $groupByObject = $this->denormalizer->denormalize(
                    $groupBy,
                    GroupBy::class,
                    $format,
                    $context
                );

                $filterData->addGroupBy($groupByObject);
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
                    throw new ApiException(new ApiErrorResponse(
                        'Each and criteria must have a uid. Example: "uid": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
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
                    throw new ApiException(new ApiErrorResponse(
                        'Each or criteria must have a uid. Example: "uid": 1',
                        ApiErrorResponse::TYPE_QUERY_ERROR,
                        [],
                        Response::HTTP_BAD_REQUEST
                    ));
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
                throw new ApiException(new ApiErrorResponse(
                    'Each join object must have a relationshipPropertyToJoinOn. Example: "relationshipPropertyToJoinOn": 11',
                    ApiErrorResponse::TYPE_QUERY_ERROR,
                    [],
                    Response::HTTP_BAD_REQUEST
                ));
            }

            if(!isset($join['joinType'])) {
                throw new ApiException(new ApiErrorResponse(
                    'Each join object must have a joinType. Example: "joinType": "With"',
                    ApiErrorResponse::TYPE_QUERY_ERROR,
                    [],
                    Response::HTTP_BAD_REQUEST
                ));
            }

            $joinObject = new Join();
            $relationshipPropertyToJoinOn = $this->propertyRepository->find($join['relationshipPropertyToJoinOn']);

            if(!$relationshipPropertyToJoinOn) {
                throw new ApiException(new ApiErrorResponse(
                    sprintf("relationshipPropertyToJoinOn: %s not not found", $join['relationshipPropertyToJoinOn']),
                    ApiErrorResponse::TYPE_QUERY_ERROR,
                    [],
                    Response::HTTP_BAD_REQUEST
                ));
            }

            $joinObject->setRelationshipPropertyToJoinOn($relationshipPropertyToJoinOn);
            $joinObject->setJoinType($join['joinType']);

            // COLUMNS
            if(isset($join['columns'])) {
                foreach($join['columns'] as $columnData) {
                    if(!isset($columnData['property'])) {
                        throw new ApiException(new ApiErrorResponse(
                            'Each column object must have a property. Example: "property": 1',
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
                    }
                    $column = new Column();
                    $property = $this->propertyRepository->find($columnData['property']);
                    if(!$property) {
                        throw new ApiException(new ApiErrorResponse(
                            sprintf("property: %s not not found", $columnData['property']),
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
                    }
                    if(isset($columnData['renameTo'])) {
                        $column->setRenameTo($columnData['renameTo']);
                    }
                    if(isset($columnData['newValue'])) {
                        $column->setNewValue($columnData['newValue']);
                    }
                    if(isset($columnData['uid'])) {
                        $column->setUid($columnData['uid']);
                    }
                    if($this->statement === 'UPDATE' && !isset($columnData['newValue'])) {
                        throw new ApiException(new ApiErrorResponse(
                            sprintf('All columns must have a newValue property when using the UPDATE Statement. Example: newValue: "super cool new value here"'),
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
                    }
                    $column->setProperty($property);
                    $joinObject->addColumn($column);
                }
            }

            // FILTERS
            if(isset($join['filters'])) {
                foreach($join['filters'] as $filter) {

                    if(!isset($filter['uid'])) {
                        throw new ApiException(new ApiErrorResponse(
                            'Each filter object must have a uid. Example: "uid": "1"',
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
                    }

                    if(!isset($filter['property'])) {
                        throw new ApiException(new ApiErrorResponse(
                            'Each filter object must have a property. Example: "property": 1',
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
                    }
                    $property = $this->propertyRepository->find($filter['property']);
                    if(!$property) {
                        throw new ApiException(new ApiErrorResponse(
                            sprintf("property: %s not not found", $filter['property']),
                            ApiErrorResponse::TYPE_QUERY_ERROR,
                            [],
                            Response::HTTP_BAD_REQUEST
                        ));
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