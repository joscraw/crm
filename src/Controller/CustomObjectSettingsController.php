<?php

namespace App\Controller;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Role;
use App\Form\CustomObjectType;
use App\Form\DeleteCustomObjectType;
use App\Form\EditCustomObjectType;
use App\Message\WorkflowMessage;
use App\Repository\CustomObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
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
 * @Route("{internalIdentifier}/objects")
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

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * CustomObjectSettingsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PermissionAuthorizationHandler $permissionAuthorizationHandler
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
    }

    /**
     * @Route(name="custom_object_settings", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal) {

     /*   $connectionFactory = new RedisConnectionFactory([
            'host' => 'localhost',
            'port' => 6379,
            'scheme_extensions' => ['predis'],
        ]);

        $context = $connectionFactory->createContext();

        $fooQueue = $context->createQueue('aQueue');
        $message = $context->createMessage('Hello world!');

        $context->createProducer()->send($fooQueue, $message);

        $name = "Josh";*/


        /*$bus->dispatch(new WorkflowMessage('A string to be sent...'));*/


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
        $arrayResults = $results['arrayResults'];
        $filteredObjectsCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredObjectsCount : $totalObjectsCount,
            'recordsTotal'  => $totalObjectsCount,
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
     * @Route("{customObject}/edit-form", name="edit_custom_object_form", methods={"GET"}, options = { "expose" = true })
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getEditCustomObjectFormAction(CustomObject $customObject) {

        $form = $this->createForm(EditCustomObjectType::class, $customObject);

        $formMarkup = $this->renderView(
            'Api/form/edit_custom_object_form.html.twig',
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
     * @Route("{customObject}/delete-form", name="delete_custom_object_form", methods={"GET"}, options = { "expose" = true })
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getDeleteCustomObjectFormAction(CustomObject $customObject) {

        $form = $this->createForm(DeleteCustomObjectType::class, $customObject);

        $formMarkup = $this->renderView(
            'Api/form/delete_custom_object_form.html.twig',
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
     * @Route("{customObject}/delete", name="delete_custom_object", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function deleteCustomObjectAction(Portal $portal, Request $request, CustomObject $customObject)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_CUSTOM_OBJECT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeleteCustomObjectType::class, $customObject);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_custom_object_form.html.twig',
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

        // delete custom object here
        /** @var $customObject CustomObject */
        $customObject = $form->getData();
        $this->entityManager->remove($customObject);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("{customObject}/edit", name="edit_custom_object", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function editCustomObjectAction(Portal $portal, Request $request, CustomObject $customObject)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::EDIT_CUSTOM_OBJECT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(EditCustomObjectType::class, $customObject);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/edit_custom_object_form.html.twig',
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

    /**
     * @Route("/create", name="create_custom_object", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomObjectAction(Portal $portal, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::CREATE_CUSTOM_OBJECT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

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