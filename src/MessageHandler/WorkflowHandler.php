<?php

namespace App\MessageHandler;

use App\Mailer\ResetPasswordMailer;
use App\Mailer\WorkflowSendEmailActionMailer;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use App\Repository\WorkflowRepository;
use App\Service\WorkflowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\WorkflowMessage;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class WorkflowHandler
 * @package App\MessageHandler
 */
class WorkflowHandler implements MessageHandlerInterface
{
    /**
     * @var WorkflowProcessor
     */
    private $workflowProcessor;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * @var ObjectWorkflowRepository
     */
    private $objectWorkflowRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * WorkflowHandler constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param UserRepository $userRepository
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        UserRepository $userRepository,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->userRepository = $userRepository;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->entityManager = $entityManager;
    }


    public function __invoke(WorkflowMessage $message)
    {
        // records can be modified in so many different ways. I think it might make more sense to have a command That loops through all the workflows and
        // adds them to a queue.

        $workflowId = $message->getContent();
        $workflow = $this->workflowRepository->find($workflowId);
        $this->workflowProcessor->run($workflow);
        echo 'completed...';
    }
}