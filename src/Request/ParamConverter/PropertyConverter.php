<?php

namespace App\Request\ParamConverter;

use App\Entity\Property;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class PropertyConverter implements ParamConverterInterface
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
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * PropertySettingsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
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
        $propertyInternalName = $request->attributes->get('propertyInternalName');

        $property = $this->propertyRepository->findByInternalNameAndPortalInternalIdentifierAndCustomObjectInternalName(
            $propertyInternalName,
            $portalInternalIdentifier,
            $customObjectInternalName
        );

        if(!$property) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $property);

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

        if($configuration->getClass() !== Property::class) {
            return false;
        }

        return true;
    }
}