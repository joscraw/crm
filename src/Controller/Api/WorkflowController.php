<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Folder;
use App\Entity\Form;
use App\Entity\MarketingList;
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
use App\Model\FieldCatalog;
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
     * @var WorkflowTriggerRepository $workflowTriggerRepository
     */
    private $workflowTriggerRepository;

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
     * @param WorkflowTriggerRepository $workflowTriggerRepository
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
        WorkflowTriggerRepository $workflowTriggerRepository
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
        $this->workflowTriggerRepository = $workflowTriggerRepository;
    }

    /**
     * @Route("{internalIdentifier}/api/workflows/{uid}/get-trigger-form", name="get_workflow_trigger_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkflowTriggerFormAction(Portal $portal, Workflow $workflow, Request $request) {

        // dummy code - this is here just so that the Task has some tags
        // otherwise, this isn't an interesting example
        /*$trigger = new WorkflowTrigger();
        $trigger->setTriggerType('test trigger type');
        $workflow->getWorkflowTriggers()->add($trigger);*/

        $workflowTrigger = new WorkflowTrigger();
        $form = $this->createForm(WorkflowTriggerType::class, $workflowTrigger, ['portal' => $portal]);

        $formMarkup = $this->renderView(
            'Api/form/workflow_trigger_form.twig',
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
     * @Route("{internalIdentifier}/api/workflows/{uid}/submit-trigger-form", name="submit_workflow_trigger_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function createTriggerAction(Portal $portal, Workflow $workflow, Request $request) {

        $options = [];

        $options = [
            'portal' => $portal,
        ];

       /* $skipValidation = $request->request->get('skip_validation', false);

        if(!$skipValidation) {
            $options['validation_groups'] = ['CREATE'];
        }*/

        $workflowTrigger = new WorkflowTrigger();
        $form = $this->createForm(WorkflowTriggerType::class, $workflowTrigger, $options);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/workflow_trigger_form.twig',
            [
                'form' => $form->createView(),
            ]
        );

        if ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var WorkflowTrigger $workflowTrigger */
            $workflowTrigger = $form->getData();
            $workflow->addWorkflowTrigger($workflowTrigger);

            $this->entityManager->persist($workflow);
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
     * @Route("/{internalIdentifier}/api/workflows/{uid}/triggers", name="get_workflow_triggers", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkflowTriggersAction(Portal $portal, Workflow $workflow, Request $request) {

        $triggers = $this->workflowTriggerRepository->findBy([
            'workflow' => $workflow->getId()
        ]);

        $json = $this->serializer->serialize($triggers, 'json', ['groups' => ['WORKFLOW_TRIGGERS', 'WORKFLOW_TRIGGER_DATA']]);

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
            [
                'id' => 1,
                'internalName' => WorkflowTriggerCatalog::PROPERTY_BASED_TRIGGER,
                'label' => 'Property Based Trigger',
            ]
        ];

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }
}