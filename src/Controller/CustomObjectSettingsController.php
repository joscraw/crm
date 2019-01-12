<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Form\CustomObjectType;
use App\Repository\CustomObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class CustomObjectSettingsController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/custom-objects")
 *
 */
class CustomObjectSettingsController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    public function __construct(EntityManagerInterface $entityManager, CustomObjectRepository $customObjectRepository)
    {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
    }

    /**
     * @Route(name="custom_object_settings", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal) {

        return $this->render('objectSettings/index.html.twig', array(
            'portal' => $portal,
        ));
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/datatable", name="custom_objects_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCustomObjectsForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->customObjectRepository->getDataTableData($start, $length, $search, $orders, $columns);

        $totalObjectsCount = $this->customObjectRepository->findCount();
        $filteredObjectsCount = $results['countResult'];
        $arrayResults = $results['arrayResults'];

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsTotal'  => $totalObjectsCount,
            'recordsFiltered'   => $filteredObjectsCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/form", name="custom_object_form", methods={"GET"}, options = { "expose" = true })
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
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/create", name="create_custom_object", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomObjectAction(Portal $portal, Request $request)
    {
        $customObject = new CustomObject();
        $customObject->setPortal($portal);

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
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        /** @var $customObject CustomObject */
        $customObject = $form->getData();
        $this->entityManager->persist($customObject);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }


}