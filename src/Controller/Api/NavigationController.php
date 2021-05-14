<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Utils\ServiceHelper;
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
    use ServiceHelper;

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