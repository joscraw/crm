<?php

namespace App\Request\ParamConverter;

use App\Entity\Workflow;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class WorkflowConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * WorkflowConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param WorkflowRepository $workflowRepository
     */
    public function __construct(EntityManagerInterface $entityManager, WorkflowRepository $workflowRepository)
    {
        $this->entityManager = $entityManager;
        $this->workflowRepository = $workflowRepository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $uid = $request->attributes->get('uid');

        $workflow = $this->workflowRepository->getWorkflowAndAssociationsByUid($uid);
        if(!$workflow) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $workflow);

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

        if($configuration->getClass() !== Workflow::class) {
            return false;
        }

        return true;
    }
}