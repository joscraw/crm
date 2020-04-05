<?php

namespace App\Controller;

use App\Entity\Portal;
use App\Entity\Report;
use App\Utils\ServiceHelper;
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
    use ServiceHelper;

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