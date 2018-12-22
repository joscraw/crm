<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Form\CustomObjectType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomObjectSettingsController
 * @package App\Controller
 *
 * @Route("/custom-object-settings/{portal}")
 *
 */
class CustomObjectSettingsController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="custom_object_settings", methods={"GET"})
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal) {

        return $this->render('objectSettings/index.html.twig', array());
    }

    /**
     * @Route("/get-custom-object-form", name="custom_object_form", methods={"GET"}, options = { "expose" = true })
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

    /**
     * @Route("/custom-objects", name="custom_object_new", methods={"POST"}, options={"expose" = true})
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newCustomObjectAction(Request $request)
    {
        $customObject = new CustomObject();

        $form = $this->createForm(CustomObjectType::class, $customObject);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/custom_object_form.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );

            return new JsonResponse(
                [
                    'success' => false,
                    'markup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $customObject = $form->getData();

        $this->entityManager->persist($customObject);
        $this->entityManager->flush($customObject);

        return new JsonResponse(
            [
                'success' => true,
            ]
        );
    }
}