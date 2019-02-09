<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PropertySettingsController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/properties")
 *
 */
class PropertySettingsController extends AbstractController
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
     * @Route("/{internalName}/{routing}", name="property_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return Response
     */
    public function indexAction(Portal $portal, CustomObject $customObject) {

        return $this->render('propertySettings/index.html.twig');
    }

}