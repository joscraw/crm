<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Report;
use App\Entity\Role;
use App\Form\CustomObjectType;
use App\Form\DeleteReportType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Service\MessageGenerator;
use App\Utils\ArrayHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class ReportController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/reports")
 */
class ReportController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;

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
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * ReportController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param SerializerInterface $serializer
     * @param ReportRepository $reportRepository
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        ReportRepository $reportRepository,
        PermissionAuthorizationHandler $permissionAuthorizationHandler
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
        $this->reportRepository = $reportRepository;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
    }

    /**
     * @Route("/{internalName}/save-report", name="save_report", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     */
    public function saveReportAction(Portal $portal, CustomObject $customObject, Request $request) {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::CREATE_REPORT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $data = $request->request->get('data', []);

        $reportName = $request->request->get('reportName', '');

        $columnOrder = $request->request->get('columnOrder', []);

        $results = $this->recordRepository->newReportLogicBuilder($data, $customObject, $columnOrder);

        $response = new JsonResponse([
            'success' => true,
            'data'  => $results['results']
        ], Response::HTTP_OK);

        return $response;

     /*   $report = new Report();
        $report->setQuery($query);
        $report->setCustomObject($customObject);
        $report->setData($data);
        $report->setName($reportName);
        $report->setPortal($portal);
        $report->setColumnOrder($columnOrder);

        $this->entityManager->persist($report);
        $this->entityManager->flush();*/

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/datatable", name="reports_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getReportsForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->reportRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns);

        $totalReportCount = $this->reportRepository->getTotalCount($portal);
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
     * @Route("/{reportId}/download", name="download_report", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Report $report
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function downloadAction(Portal $portal, Report $report) {

        $em = $this->entityManager;
        $stmt = $em->getConnection()->prepare($report->getQuery());
        $stmt->execute();
        $results = $stmt->fetchAll();

        $response = new Response($this->serializer->encode($results, 'csv'));

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$report->getName()}.csv");

        return $response;

    }

    /**
     * @Route("/{reportId}/delete-form", name="delete_report_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Report $report
     * @return JsonResponse
     */
    public function getDeleteReportFormAction(Portal $portal, Report $report) {

        $form = $this->createForm(DeleteReportType::class, $report);

        $formMarkup = $this->renderView(
            'Api/form/delete_report_form.html.twig',
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
     * @Route("/{reportId}/delete", name="delete_report", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Report $report
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteReportAction(Portal $portal, Report $report, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_REPORT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeleteReportType::class, $report);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_report_form.html.twig',
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
        $report = $form->getData();
        $this->entityManager->remove($report);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/{reportId}", name="get_report", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Report $report
     * @param Request $request
     * @return JsonResponse
     */
    public function getReportAction(Portal $portal, Report $report, Request $request) {

        $report = $this->reportRepository->find($report->getId());

        $json = $this->serializer->serialize($report, 'json', ['groups' => ['REPORT']]);

        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalName}/{reportId}/edit-report-save", name="api_edit_report", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Report $report
     * @param Request $request
     * @return Response
     */
    public function editReportAction(Portal $portal, CustomObject $customObject, Report $report, Request $request) {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::EDIT_REPORT,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $data = $request->request->get('data', []);

        $reportName = $request->request->get('reportName', '');

        $columnOrder = $request->request->get('columnOrder', []);

        $query = $this->recordRepository->getReportMysqlOnly($data, $customObject, $columnOrder);

        $report->setQuery($query);
        $report->setCustomObject($customObject);
        $report->setData($data);
        $report->setName($reportName);
        $report->setPortal($portal);
        $report->setColumnOrder($columnOrder);

        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/report-preview", name="get_report_preview", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportPreviewAction(Portal $portal, CustomObject $customObject, Request $request) {

        $data = $request->request->get('data', []);

        $columnOrder = $request->request->get('columnOrder', []);

        $results = $this->recordRepository->getReportData($data, $customObject, $columnOrder);

        $response = new JsonResponse([
            'success' => true,
            'data'  => $results['results']
        ], Response::HTTP_OK);

        return $response;

    }

}