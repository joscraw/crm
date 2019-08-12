<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Form;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Report;
use App\Entity\Workflow;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\MessageGenerator;
use App\Utils\RandomStringGenerator;
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
 * Class WorkflowController
 * @package App\Controller
 */
class WorkflowController extends AbstractController
{
    use RandomStringGenerator;

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
     * RecordController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @Route("/{internalIdentifier}/workflows/type", name="workflow_type", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAction(Portal $portal) {

        return $this->render('workflow/type.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/object", name="workflow_object", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function objectAction(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/object.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/triggers", name="workflow_trigger", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function triggersAction(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/trigger.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{uid}/actions", name="workflow_action", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Workflow $workflow
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function actions(Portal $portal, Workflow $workflow) {

        return $this->render('workflow/action.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{internalIdentifier}/workflows/{routing}", name="workflow_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workflowSettings(Portal $portal) {

        return $this->render('workflow/settings.html.twig', array(
            'portal' => $portal
        ));
    }
}