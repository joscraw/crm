<?php

namespace App\Controller;

use App\Entity\Portal;
use App\Entity\Report;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ReportController
 * @package App\Controller
 *
 * @Route("/{internalIdentifier}/reports")
 *
 */
class ReportController extends AbstractController
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
     * @Route("/create/{routing}", name="create_report", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Portal $portal) {

        return $this->render('report/create.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{reportId}/edit/{routing}", name="edit_report", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Report $report
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Report $report, Portal $portal) {

        return $this->render('report/edit.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{routing}", name="report_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reportSettingsAction(Portal $portal) {

        return $this->render('report/settings.html.twig', array(
            'portal' => $portal
        ));
    }

}