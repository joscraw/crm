<?php

namespace App\Command;

use App\Repository\RecordRepository;
use App\Service\WorkflowProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Enqueue\Redis\RedisConnectionFactory;

class WorkflowProcessorCommand extends Command
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
        parent::__construct();
    }

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:workflow:process';

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connectionFactory = new RedisConnectionFactory([
            'host' => 'localhost',
            'port' => 6379,
            'scheme_extensions' => ['predis'],
        ]);

        $context = $connectionFactory->createContext();

        $fooQueue = $context->createQueue('workflowQueue');
        $consumer = $context->createConsumer($fooQueue);
        $message = $consumer->receive();

        // process a message
        $consumer->acknowledge($message);
        $recordId = $message->getBody();
        $record = $this->recordRepository->find($recordId);

        $this->workflowProcessor->run($record);

        $output->writeln([
            'workflows finished',
            '============',
            '',
        ]);
    }
}