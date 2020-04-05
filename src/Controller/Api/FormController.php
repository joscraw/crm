<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Form;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\Record;
use App\Entity\Report;
use App\Form\DeleteFormType;
use App\Form\FormEditorEditOptionsType;
use App\Form\FormType;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\FormRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use App\Utils\ListFolderBreadcrumbs;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\PropertyHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class FormController
 * @package App\Controller\Api
 */
class FormController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;
    use RandomStringGenerator;
    use PropertyHelper;

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
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FormRepository
     */
    private $formRepository;

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * @var MarketingListRepository
     */
    private $marketingListRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var ListFolderBreadcrumbs
     */
    private $folderBreadcrumbs;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * FormController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param SerializerInterface $serializer
     * @param FormRepository $formRepository
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     * @param MarketingListRepository $marketingListRepository
     * @param FolderRepository $folderRepository
     * @param ListFolderBreadcrumbs $folderBreadcrumbs
     * @param DenormalizerInterface $denormalizer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        FormRepository $formRepository,
        PermissionAuthorizationHandler $permissionAuthorizationHandler,
        MarketingListRepository $marketingListRepository,
        FolderRepository $folderRepository,
        ListFolderBreadcrumbs $folderBreadcrumbs,
        DenormalizerInterface $denormalizer
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
        $this->formRepository = $formRepository;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
        $this->marketingListRepository = $marketingListRepository;
        $this->folderRepository = $folderRepository;
        $this->folderBreadcrumbs = $folderBreadcrumbs;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @Route("{internalIdentifier}/api/forms/initialize", name="initialize_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeFormAction(Portal $portal, Request $request) {

        $customObjectId = $request->request->get('customObjectId', null);
        $customObject = $this->customObjectRepository->find($customObjectId);
        $form = new Form();
        $form->setType(Form::REGULAR_FORM);
        $form->setPortal($portal);
        $form->setCustomObject($customObject);
        $form->setUid($this->generateRandomString(40));
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($form, 'json', ['groups' => ['FORMS']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/api/forms/{uid}/form-data", name="get_form_data", methods={"GET"}, options = { "expose" = true })
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormDataAction(Form $form, Request $request) {

        $json = $this->serializer->serialize($form, 'json', ['groups' => ['FORMS']]);

        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * This is the final form the user actually ses on the frontend to fill out
     *
     * @Route("/api/forms/{uid}/get-form", name="get_form", methods={"GET"}, options = { "expose" = true })
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormAction(Form $form, Request $request) {

        $properties = $form->getData();

        $properties = $this->setValidPropertyTypes($properties);

        foreach ($properties as &$property) {
            $property = $this->serializer->deserialize(json_encode($property, true), Property::class, 'json');
        }

        $form = $this->createForm(FormType::class, null, [
            'properties' => $properties,
            'showCaptcha' => $form->getRecaptcha()
        ]);

        $formMarkup = $this->renderView(
            'Api/form/form_editor_form.html.twig',
            [
                'form' => $form->createView(),
                'properties' => $properties
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
     * @Route("{internalIdentifier}/api/forms/{uid}/get-edit-options-form", name="get_edit_options_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function getEditOptionsFormAction(Portal $portal, Form $form, Request $request) {

        $form = $this->createForm(FormEditorEditOptionsType::class, $form, []);

        $formMarkup = $this->renderView(
            'Api/form/form_editor_edit_options_form.html.twig',
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
     * @Route("{internalIdentifier}/api/forms/{uid}/submit-edit-options-form", name="submit_edit_options_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $formObj
     * @param Request $request
     * @return JsonResponse
     */
    public function submitEditOptionsFormAction(Portal $portal, Form $formObj, Request $request) {

        $form = $this->createForm(FormEditorEditOptionsType::class, $formObj, []);
        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/form_editor_edit_options_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        if ($form->isSubmitted() && !$form->isValid()) {

            if(!$form->isValid()) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup,
                    ], Response::HTTP_BAD_REQUEST
                );
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $form Form */
            $formObj = $form->getData();
            $this->entityManager->persist($formObj);
            $this->entityManager->flush();
        }

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/api/forms/{uid}/submit-form", name="form_submit", methods={"POST"}, options = { "expose" = true })
     * @param Form $formEntity
     * @param Request $request
     * @return JsonResponse
     */
    public function submitFormAction(Form $formEntity, Request $request) {

        $properties = $formEntity->getData();

        $properties = $this->setValidPropertyTypes($properties);

        foreach ($properties as &$property) {
            $property = $this->serializer->deserialize(json_encode($property, true), Property::class, 'json');
        }

        $form = $this->createForm(FormType::class, null, [
            'properties' => $properties,
            'showCaptcha' => $formEntity->getRecaptcha()
        ]);


        $form->handleRequest($request);

        if (!$form->isValid()) {

            $formMarkup = $this->renderView(
                'Api/form/form_editor_form.html.twig',
                [
                    'form' => $form->createView(),
                    'properties' => $properties
                ]
            );

            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $data = $form->getData();

        $record = new Record();
        $record->setCustomObject($formEntity->getCustomObject());
        $record->setProperties($data);
        $record->setProperties($form->getData());
        $this->entityManager->persist($record);

        $formEntity->incrementSubmissionCount();
        $this->entityManager->persist($formEntity);

        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalIdentifier}/api/forms/{uid}/save-form", name="save_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFormAction(Portal $portal, Form $form, Request $request) {

        $formData = $request->request->get('form', null);
        $data = !empty($formData['draft']) ? $formData['draft'] : [];
        $name = !empty($formData['name']) ? $formData['name'] : '';


        $form->setDraft($data);
        $form->setName($name);
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;

    }

    /**
     * @Route("/{internalIdentifier}/api/forms/{uid}/publish-form", name="publish_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function publishFormAction(Portal $portal, Form $form, Request $request) {

        $form->setPublished(true);
        $form->setData($form->getDraft());
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;

    }

    /**
     * @Route("/{internalIdentifier}/api/forms/{uid}/form-preview", name="form_preview", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormPreviewAction(Portal $portal, CustomObject $customObject, Request $request) {

        $formData = $request->request->get('form', null);
        $properties = !empty($formData['draft']) ? $formData['draft'] : [];

        $properties = $this->setValidPropertyTypes($properties);

        foreach ($properties as &$property) {
            $property = $this->serializer->deserialize(json_encode($property, true), Property::class, 'json');
        }

        $form = $this->createForm(FormType::class, null, [
            'properties' => $properties,
            'isPreview' => true
        ]);

        $formMarkup = $this->renderView(
            'Api/form/form_editor_preview_form.html.twig',
            [
                'form' => $form->createView(),
                'properties' => $properties
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
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/{internalIdentifier}/api/forms/datatable", name="forms_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getFormsForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->formRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns);

        $totalReportCount = $this->formRepository->getTotalCount($portal);
        $arrayResults = $results['arrayResults'];
        $filteredReportCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredReportCount : $totalReportCount,
            'recordsTotal'  => $totalReportCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalIdentifier}/api/forms/{uid}/delete-form", name="get_delete_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function getDeleteFormAction(Portal $portal, Form $form, Request $request) {

        $form = $this->createForm(DeleteFormType::class, $form);

        $formMarkup = $this->renderView(
            'Api/form/delete_form_form.html.twig',
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
     * @Route("/{internalIdentifier}/api/forms/{uid}/delete", name="delete_form", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFormAction(Portal $portal, Form $form, Request $request)
    {

        $form = $this->createForm(DeleteFormType::class, $form);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_form_form.html.twig',
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

        // delete report here
        /** @var $report Report */
        $form = $form->getData();
        $this->entityManager->remove($form);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }
}