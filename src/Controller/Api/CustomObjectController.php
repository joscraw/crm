<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Form\CustomObjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomObjectController
 * @package App\Controller\API
 *
 * @Route("/api")
 */
class CustomObjectController extends AbstractController
{
    /**
     * @Route("/get-custom-object-form", name="app_get_custom_object_form", methods={"GET"}, options = { "expose" = true })
     *
     */
    public function getCustomObjectFormAction() {

        $customObject = new CustomObject();

        $form = $this->createForm(CustomObjectType::class, $customObject);

        $formMarkup = $this->renderView(
            'Api/form/custom_object_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ]
        );

    }


}