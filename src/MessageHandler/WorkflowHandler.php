<?php

namespace App\MessageHandler;

use App\Repository\RecordRepository;
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
     * WorkflowProcessorCommand constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     */
    public function __construct(WorkflowProcessor $workflowProcessor, RecordRepository $recordRepository)
    {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
    }


    public function __invoke(WorkflowMessage $message)
    {
        $recordId = $message->getContent();
        $record = $this->recordRepository->find($recordId);
        $this->workflowProcessor->run($record);
        echo 'completed...';
    }
}