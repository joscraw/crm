<?php

namespace App\MessageHandler;

use App\Mailer\ResetPasswordMailer;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use App\Service\WorkflowProcessor;
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
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * WorkflowHandler constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param ResetPasswordMailer $resetPasswordMailer
     * @param UserRepository $userRepository
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        ResetPasswordMailer $resetPasswordMailer,
        UserRepository $userRepository
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->resetPasswordMailer = $resetPasswordMailer;
        $this->userRepository = $userRepository;
    }


    public function __invoke(WorkflowMessage $message)
    {
        $recordId = $message->getContent();
        $record = $this->recordRepository->find($recordId);
        $this->workflowProcessor->run($record);
        echo 'completed...';
    }
}