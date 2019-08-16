<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
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
use App\Entity\Workflow;
use App\Entity\WorkflowTrigger;
use App\Form\CustomObjectType;
use App\Form\DeleteFormType;
use App\Form\DeleteListType;
use App\Form\DeleteReportType;
use App\Form\FolderType;
use App\Form\FormEditorEditOptionsType;
use App\Form\FormType;
use App\Form\MoveListToFolderType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Form\WorkflowTriggerType;
use App\Form\WorkflowType;
use App\Model\AbstractField;
use App\Model\AbstractTrigger;
use App\Model\FieldCatalog;
use App\Model\PropertyTrigger;
use App\Model\SetPropertyValueAction;
use App\Model\WorkflowTriggerCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\FormRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Repository\WorkflowTriggerRepository;
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
        $this->entityManager->persist($workflow);
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

        $draft = $request->request->get('workflow')['draft'];

        if(!isset($draft['actions'])) {
            $draft['actions'] = [];
        }

        if(!isset($draft['triggers'])) {
            $draft['triggers'] = [];
        }

        $workflow->setDraft($draft);
        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
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
        $triggers = $workflow->getDraft()['triggers'];
        $actions = $workflow->getDraft()['actions'];
        $name = $workflow->getDraft()['name'];
        $workflow->setTriggers($triggers);
        $workflow->setActions($actions);
        $workflow->setName($name);

        $this->entityManager->persist($workflow);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
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

        $json = $this->serializer->serialize($workflow, 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'SELECTABLE_PROPERTIES', 'WORKFLOW_ACTION']]);
        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
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
            json_decode($this->serializer->serialize(new PropertyTrigger(), 'json', ['groups' => ['TRIGGER']]), true)
        ];

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
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
            json_decode($this->serializer->serialize(new SetPropertyValueAction(), 'json', ['groups' => ['WORKFLOW_ACTION', 'TRIGGER']]), true)
        ];

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
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
            'data'  => Workflow::$types
        ], Response::HTTP_OK);
    }
}