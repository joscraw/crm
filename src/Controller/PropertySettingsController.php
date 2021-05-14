<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Utils\ServiceHelper;
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
    use ServiceHelper;

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