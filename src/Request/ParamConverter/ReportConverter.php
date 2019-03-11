<?php

namespace App\Request\ParamConverter;

use App\Entity\Property;
use App\Entity\Record;
use App\Entity\Report;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class ReportConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * ReportConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param ReportRepository $reportRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ReportRepository $reportRepository)
    {
        $this->entityManager = $entityManager;
        $this->reportRepository = $reportRepository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $reportId = $request->attributes->get('reportId');

        $report = $this->reportRepository->find($reportId);

        if(!$report) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $report);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== Report::class) {
            return false;
        }

        return true;
    }
}