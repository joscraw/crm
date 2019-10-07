<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\Action;
use App\Entity\CustomObject;
use App\Entity\Filter;
use App\Entity\Folder;
use App\Entity\Form;
use App\Entity\MarketingList;
use App\Entity\ObjectWorkflow;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Report;
use App\Entity\Role;
use App\Entity\SendEmailAction;
use App\Entity\Trigger;
use App\Entity\TriggerFilter;
use App\Entity\Workflow;
use App\Form\CustomObjectType;
use App\Form\DeleteFormType;
use App\Form\DeleteListType;
use App\Form\DeleteReportType;
use App\Form\DeleteWorkflowType;
use App\Form\FolderType;
use App\Form\FormEditorEditOptionsType;
use App\Form\FormType;
use App\Form\MoveListToFolderType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\TriggerFilterRepository;
use App\Repository\WorkflowRepository;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use App\Form\WorkflowType;
use App\Model\AbstractField;
use App\Model\FieldCatalog;
use App\Entity\PropertyTrigger;
use App\Entity\SetPropertyValueAction;
use App\Model\WorkflowTriggerCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\FormRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Service\MessageGenerator;
use App\Utils\ArrayHelper;
use App\Utils\ListFolderBreadcrumbs;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\PropertyHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class WorkflowController
 * @package App\Controller\Api
 */
class WorkflowController extends ApiController
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
     * @var TriggerFilterRepository
     */
    private $triggerFilterRepository;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * @var ObjectWorkflowRepository
     */
    private $objectWorkflowRepository;

    /**
     * WorkflowController constructor.
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
     * @param TriggerFilterRepository $triggerFilterRepository
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
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
        DenormalizerInterface $denormalizer,
        TriggerFilterRepository $triggerFilterRepository,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository
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
        $this->triggerFilterRepository = $triggerFilterRepository;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/{internalIdentifier}/api/workflows/datatable", name="workflows_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getWorkflowsForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->workflowRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns);

        $totalWorkflowCount = $this->formRepository->getTotalCount($portal);
        $arrayResults = $results['arrayResults'];
        $filteredReportCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredReportCount : $totalWorkflowCount,
            'recordsTotal'  => $totalWorkflowCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;
    }



    /**
     * @Route("{internalIdentifier}/api/workflows/initialize", name="initialize_workflow", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeWorkflowAction(Portal $portal, Request $request) {

        $workflowType = $request->request->get('workflowType', null);

        switch ($workflowType) {
            case Workflow::OBJECT_WORKFLOW:
                $workflow = new ObjectWorkflow();
                $workflow->setPortal($portal);
                $workflow->setUid($this->generateRandomString(40));
                break;
            default:
                throw new NotFoundHttpException("Workflow type not found");
                break;
        }

        $this->entityManager->persist($workflow);
        $this->entityManager->flush();
        $this->entityManager->refresh($workflow);

        $publishedWorkflow = clone $workflow;
        $publishedWorkflow->setUid($this->generateRandomString(40));
        $publishedWorkflow->setDraft(false);
        $this->entityManager->persist($publishedWorkflow);
        $this->entityManager->flush();

        $workflow->setPublishedWorkflow($publishedWorkflow);
        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($workflow, 'json', ['groups' => ['WORKFLOW']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data'  => $payload
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("{internalIdentifier}/api/workflows/{uid}/add-custom-object", name="workflow_add_custom_object", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param ObjectWorkflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function workflowAddCustomObjectAction(Portal $portal, ObjectWorkflow $workflow, Request $request) {

        $customObjectId = $request->request->get('customObjectId', null);

        $customObject = $this->customObjectRepository->find($customObjectId);

        $workflow->setCustomObject($customObject);
        $publishedWorkflow = $workflow->getPublishedWorkflow();
        $publishedWorkflow->setCustomObject($customObject);

        $this->entityManager->persist($workflow);
        $this->entityManager->persist($publishedWorkflow);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($workflow, 'json', ['groups' => ['WORKFLOW']]);

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
     * @Route("{internalIdentifier}/api/workflows/{uid}/save", name="save_workflow", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function saveWorkflowAction(Portal $portal, Workflow $workflow, Request $request) {

        /* SETUP THE TRIGGERS */
        foreach($workflow->getTriggers() as $trigger) {
            $this->entityManager->remove($trigger);
            $this->entityManager->flush();
        }

        $workflowArray = $request->request->get('workflow');
        $triggers = !empty($workflowArray['triggers']) ? $workflowArray['triggers'] : [];
        foreach($triggers as $trigger) {

            $trigger['filters'] = $this->setValidPropertyTypes(!empty($trigger['filters']) ? $trigger['filters'] : []);

            /** @var Trigger $trigger */
            $trigger = $this->serializer->deserialize(json_encode($trigger, true), Trigger::class, 'json');

            switch ($trigger->getName()) {
                case Trigger::PROPERTY_BASED_TRIGGER:
                    /** @var PropertyTrigger $trigger */
                    $trigger->setWorkflow($workflow);
                    foreach($trigger->getFilters() as $filter) {
                        if($filter->getProperty()) {
                            $property = $this->propertyRepository->find($filter->getProperty()->getId());
                            $filter->setProperty($property);
                        }
                    }
                    break;
            }
            $this->entityManager->persist($trigger);
        }

        /* SETUP THE ACTIONS */
        foreach($workflow->getActions() as $action) {
            $this->entityManager->remove($action);
            $this->entityManager->flush();
        }

        $workflowArray = $request->request->get('workflow');
        $actions = !empty($workflowArray['actions']) ? $workflowArray['actions'] : [];
        foreach($actions as $action) {

            /** @var Action $action */
            $action = $this->serializer->deserialize(json_encode($action, true), Action::class, 'json');
            $action->setWorkflow($workflow);

            // Just in case you meed some custom processing per action type
            switch ($action->getName()) {
                case Action::SET_PROPERTY_VALUE_ACTION:
                    /** @var SetPropertyValueAction $action */
                    $property = $this->propertyRepository->find($action->getProperty()->getId());
                    $action->setProperty($property);
                    break;
                case Action::SEND_EMAIL_ACTION:
                    /** @var SendEmailAction $action */
                    break;
            }

            $this->entityManager->persist($action);
        }

        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        // let's pull a fresh copy from the database
        $this->entityManager->refresh($workflow);

        return new JsonResponse(
            [
                'success' => true,
                'data'  => $this->getWorkflowDataResponse($workflow)
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalIdentifier}/api/workflows/{uid}/delete", name="delete_workflow", options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteWorkflowAction(Portal $portal, Workflow $workflow, Request $request) {

        $form = $this->createForm(DeleteWorkflowType::class, $workflow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // delete workflow here
            /** @var $workflow Workflow */
            $workflow = $form->getData();
            $this->entityManager->remove($workflow);
            $this->entityManager->flush();
            $this->entityManager->remove($workflow->getPublishedWorkflow());
            $this->entityManager->flush();

            return new JsonResponse(
                [
                    'success' => true,
                ],
                Response::HTTP_OK
            );
        }

        $formMarkup = $this->renderView(
            'Api/form/delete_workflow_form.html.twig',
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
     * @Route("{internalIdentifier}/api/workflows/{uid}/start-pause", name="start_pause_workflow", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function startPause(Portal $portal, Workflow $workflow, Request $request) {

        $startPause = $request->request->get('workflow')['paused'] === 'true'? true: false;
        $workflow->setPaused($startPause);

        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        // let's pull a fresh copy from the database
        $this->entityManager->refresh($workflow);

        return new JsonResponse(
            [
                'success' => true,
                'data'  => $this->getWorkflowDataResponse($workflow)
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("{internalIdentifier}/api/workflows/{uid}/publish", name="publish_workflow", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function publishWorkflowAction(Portal $portal, Workflow $workflow, Request $request) {

        $workflow->setPublished(true);
        $name = $request->request->get('workflow')['name'];
        $workflow->setName($name);
        $publishedWorkflow  = $workflow->getPublishedWorkflow();
        $publishedWorkflow->setName($name);

        $this->entityManager->persist($publishedWorkflow);
        $this->entityManager->flush();
        $this->entityManager->refresh($publishedWorkflow);


        /* Action Logic */
        foreach($publishedWorkflow->getActions() as $action) {
            $this->entityManager->remove($action);
            $this->entityManager->flush();
        }

        $actions = $workflow->getActions();
        foreach($actions as $action) {
            /* @var Action $action */
            switch ($action->getName()) {
                case Action::SET_PROPERTY_VALUE_ACTION:
                    /* @var SetPropertyValueAction $action */
                    $clonedAction = new SetPropertyValueAction();
                    $clonedAction->setProperty($action->getProperty());
                    $clonedAction->setName($action->getName());
                    $clonedAction->setDescription($action->getDescription());
                    $clonedAction->setUid($action->getUid());
                    $clonedAction->setValue($action->getValue());
                    $clonedAction->setJoins($action->getJoins());
                    $clonedAction->setOperator($action->getOperator());
                    $clonedAction->setWorkflow($publishedWorkflow);
                    $this->entityManager->persist($clonedAction);
                    break;
                case Action::SEND_EMAIL_ACTION:
                    /* @var SendEmailAction $action */
                    $clonedAction = new SendEmailAction();
                    $clonedAction->setToAddresses($action->getToAddresses());
                    $clonedAction->setName($action->getName());
                    $clonedAction->setUid($action->getUid());
                    $clonedAction->setSubject($action->getSubject());
                    $clonedAction->setBody($action->getBody());
                    $clonedAction->setWorkflow($publishedWorkflow);
                    $this->entityManager->persist($clonedAction);
                    break;
            }

            $this->entityManager->flush();
        }

        /* Trigger Logic */
        foreach($publishedWorkflow->getTriggers() as $trigger) {
            $this->entityManager->remove($trigger);
            $this->entityManager->flush();
        }

        foreach($workflow->getTriggers() as $trigger) {
            switch ($trigger->getName()) {
                case Trigger::PROPERTY_BASED_TRIGGER:
                    /** @var PropertyTrigger $clonedTrigger*/
                    $clonedTrigger = new PropertyTrigger();
                    $clonedTrigger->setName($trigger->getName());
                    $clonedTrigger->setDescription($trigger->getDescription());
                    $clonedTrigger->setUid($trigger->getUid());
                    $clonedTrigger->setWorkflow($publishedWorkflow);
                    $this->entityManager->persist($clonedTrigger);

                    /** @var TriggerFilter $filter */
                    foreach($trigger->getFilters() as $filter) {
                        /** @var TriggerFilter $clonedFilter */
                        $clonedFilter = new TriggerFilter();
                        $clonedFilter->setUid($filter->getUid());
                        $clonedFilter->setOperator($filter->getOperator());
                        $clonedFilter->setJoins($filter->getJoins());
                        $clonedFilter->setValue($filter->getValue());
                        $clonedFilter->setProperty($filter->getProperty());
                        $clonedFilter->setAndFilters($filter->getAndFilters());
                        $clonedFilter->setReferencedFilterPath($filter->getReferencedFilterPath());
                        $clonedFilter->setPropertyTrigger($clonedTrigger);
                        $this->entityManager->persist($clonedFilter);
                    }
                    break;
            }
        }

        $this->entityManager->persist($publishedWorkflow);
        $this->entityManager->flush();
        $this->entityManager->refresh($workflow);
        $this->entityManager->refresh($publishedWorkflow);

        return new JsonResponse(
            [
                'success' => true,
                'data'  => $this->getWorkflowDataResponse($workflow)
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalIdentifier}/api/workflows/{uid}/get", name="get_workflow", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkflowAction(Portal $portal, Workflow $workflow, Request $request) {

        return new JsonResponse([
            'success' => true,
            'data'  => $this->getWorkflowDataResponse($workflow)
        ], Response::HTTP_OK);

    }

    /**
     * @Route("/{internalIdentifier}/get-trigger-types", name="get_trigger_types", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function getTriggerTypes(Portal $portal, Request $request) {

        $payload = [
            json_decode($this->serializer->serialize(new PropertyTrigger(), 'json', ['groups' => ['TRIGGER']]), true),
        ];

        return new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalIdentifier}/action-types", name="get_action_types", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function getActionTypes(Portal $portal, Request $request) {

        $payload = [
            json_decode($this->serializer->serialize(new SetPropertyValueAction(), 'json', ['groups' => ['WORKFLOW_ACTION', 'TRIGGER']]), true),
            json_decode($this->serializer->serialize(new SendEmailAction(), 'json', ['groups' => ['WORKFLOW_ACTION', 'TRIGGER']]), true),
        ];

        return new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalIdentifier}/workflow-types", name="workflow_types", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function workflowTypes(Portal $portal, Request $request) {

        return new JsonResponse([
            'success' => true,
            'data'  => Workflow::$types,
        ], Response::HTTP_OK);
    }

    /**
     * @param Workflow $workflow
     * @return array
     */
    private function getWorkflowDataResponse(Workflow $workflow) {

        $this->entityManager->refresh($workflow);
        $this->entityManager->refresh($workflow->getPublishedWorkflow());

        foreach($workflow->getPublishedWorkflow()->getTriggers() as $trigger) {
            $this->entityManager->refresh($trigger);
        }

        $data = [];
        $json = $this->serializer->serialize($workflow, 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'WORKFLOW_ACTION']]);
        $data['workflow'] = json_decode($json, true);

        $json = $this->serializer->serialize($workflow, 'json', ['groups' => ['MD5_HASH_WORKFLOW']]);
        //$i = json_decode($json, true);
        $data['workflowHash'] = md5($json);

        $json = $this->serializer->serialize($workflow->getPublishedWorkflow(), 'json', ['groups' => ['MD5_HASH_WORKFLOW']]);
        //$y = json_decode($json, true);
        $data['publishedWorkflowHash'] = md5($json);

        if($workflow->getPublishedWorkflow()) {
            $json = $this->serializer->serialize($workflow->getPublishedWorkflow(), 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'WORKFLOW_ACTION']]);
            $data['publishedWorkflow'] = json_decode($json, true);
        } else {
            $data['publishedWorkflow'] = [
                'name' => '',
                'triggers' => [],
                'actions' => [],
            ];
        }

        return $data;
    }

}