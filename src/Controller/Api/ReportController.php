<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Report;
use App\Entity\Role;
use App\Form\DeleteReportType;
use App\Utils\ArrayHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

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
    use ServiceHelper;

    /**
     * @Route("/{internalName}/save-report", name="save_report", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
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
        $reportName = $data['reportName'];
        $query = $this->recordRepository->newReportLogicBuilder($data, $customObject, true);

        $report = new Report();
        $report->setQuery($query);
        $report->setCustomObject($customObject);
        $report->setData($data);
        $report->setName($reportName);
        $report->setPortal($portal);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
        $data['reportId'] = $report->getId();
        $report->setData($data);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true,
            'reportId' => $report->getId()
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/{reportId}/edit-report-save", name="api_edit_report", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Report $report
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
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
        $reportName = $data['reportName'];
        $query = $this->recordRepository->newReportLogicBuilder($data, $customObject, true);
        $report->setQuery($query);
        $report->setCustomObject($customObject);
        $report->setData($data);
        $report->setName($reportName);
        $report->setPortal($portal);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/results", name="get_report_results", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReportResults(Portal $portal, CustomObject $customObject, Request $request) {
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
        $results = $this->recordRepository->newReportLogicBuilder($data, $customObject);
        $response = new JsonResponse([
            'success' => true,
            'data'  => $results['results']
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
            'data'  => $payload['data']
        ], Response::HTTP_OK);
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

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/{internalName}/datatable", name="report_records_for_datatable", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRecordsForDatatableAction(Portal $portal, CustomObject $customObject, Request $request) {
        $draw = intval($request->request->get('draw'));
        $start = $request->request->get('start');
        $length = $request->request->get('length');
        $search = $request->request->get('search');
        $orders = $request->request->get('order');
        $columns = $request->request->get('columns');
        $data = $request->request->get('data', []);
        $results = $this->recordRepository->newReportLogicBuilder(
            $data, $customObject,  false, $start, $length, $search, $orders, $columns
        );
        $countQuery = $this->recordRepository->findCountByCustomObject($customObject);
        $totalRecordsCount = !empty($countQuery[0]['count']) ? $countQuery[0]['count'] : 0;
        $results = $results['results'];
        $filteredRecordsCount = $this->recordRepository->newReportLogicBuilderCount(
            $data, $customObject,  false, false, false, $search, $orders, $columns
        );
        $response = new JsonResponse([
            'success' => true,
            'data'  => $results,
            'draw'  => $draw,
            'recordsFiltered' => $filteredRecordsCount,
            'recordsTotal'  => $totalRecordsCount,
        ], Response::HTTP_OK);

        return $response;
    }
}