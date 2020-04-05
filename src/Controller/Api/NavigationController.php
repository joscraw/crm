<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class NavigationController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/navigation")
 */
class NavigationController extends ApiController
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
     * @Route("/get-side-navigation-menu", name="get_side_navigation_menu", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getSideNavigationMenuAction(Portal $portal, Request $request) {

        $customObjects = $this->customObjectRepository->findBy([
            'portal' => $portal->getId()
        ]);


        $markup = $this->renderView(
            'Api/navigation/side_navigation_menu.html.twig',
            [
                'customObjects' => $customObjects,
                'portal' => $portal
            ]
        );


        return new JsonResponse(
            [
                'success' => false,
                'markup' => $markup
            ], Response::HTTP_OK
        );

    }
}