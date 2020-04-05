<?php

namespace App\Request\ParamConverter;

use App\Entity\Record;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class RecordConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * RecordConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository
    ) {
        $this->entityManager = $entityManager;
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
    }


    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $portalInternalIdentifier = $request->attributes->get('internalIdentifier');
        $customObjectInternalName = $request->attributes->get('internalName');
        $recordId = $request->attributes->get('recordId');

        $record = $this->recordRepository->find($recordId);

        if(!$record) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $record);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== Record::class) {
            return false;
        }

        return true;
    }
}