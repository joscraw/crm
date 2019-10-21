<?php

namespace App\Command;

use App\Message\WorkflowMessage;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use App\Service\WorkflowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class WorkflowProcessorCommand
 * @package App\Command
 */
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
     * @var MessageBusInterface $bus
     */
    private $bus;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:workflow:process';

    /**
     * WorkflowProcessorCommand constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;

        parent::__construct();
    }

    protected function configure()
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workflows = $this->workflowRepository->findBy([
            'published' => true,
            'draft' => true,
            'paused' => false
        ]);

        foreach($workflows as $workflow) {
            $this->bus->dispatch(new WorkflowMessage($workflow->getId()));
            $output->writeln([sprintf('workflow %s added to queue...', $workflow->getId()), '============', '',]);
        }

        $output->writeln([
            'All workflows have been added to the queue.',
            '============',
            '',
        ]);
    }
}