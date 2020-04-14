<?php

namespace App\Command;

use App\Entity\Workflow;
use App\Entity\WorkflowEnrollment;
use App\Model\WorkflowTrigger;
use App\Repository\RecordRepository;
use App\Repository\WorkflowEnrollmentRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class WorkflowEnrollmentCommand
 * @package App\Command
 */
class WorkflowEnrollmentCommand extends Command
{
    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var WorkflowEnrollmentRepository
     */
    private $workflowEnrollmentRepository;

    protected static $defaultName = 'app:workflow:enrollment';

    /**
     * WorkflowEnrollmentCommand constructor.
     * @param RecordRepository $recordRepository
     * @param WorkflowRepository $workflowRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     * @param WorkflowEnrollmentRepository $workflowEnrollmentRepository
     */
    public function __construct(
        RecordRepository $recordRepository,
        WorkflowRepository $workflowRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        SerializerInterface $serializer,
        WorkflowEnrollmentRepository $workflowEnrollmentRepository
    ) {
        $this->recordRepository = $recordRepository;
        $this->workflowRepository = $workflowRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->serializer = $serializer;
        $this->workflowEnrollmentRepository = $workflowEnrollmentRepository;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('Determines which records need to be enrolled into all the workflows.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        gc_enable();

        $workflows = $this->workflowRepository->findBy([
            'paused' => false,
        ]);

        $batchSize = 20;
        /** @var Workflow $workflow */
        foreach($workflows as $workflow) {

            // We are clearing the entity manager with each batch so we need to pull a fresh workflow object
            $workflow = $this->workflowRepository->find($workflow->getId());

            switch ($workflow->getWorkflowTrigger()) {
                case WorkflowTrigger::PROPERTY_TRIGGER:
                    $results = $workflow->query($this->serializer, $this->entityManager);

                    $index = 0;
                    foreach($this->recordGenerator($results) as $recordId) {
                        $record = $this->recordRepository->find($recordId);

                        if(!$record) {
                            continue;
                        }

                        $enrolledWorkflow = $this->workflowEnrollmentRepository->findOneBy([
                            'record' => $record,
                            'workflow' => $workflow,
                        ]);

                        if(!$enrolledWorkflow) {
                            $workflowEnrollment = new WorkflowEnrollment();
                            $workflowEnrollment->setWorkflow($workflow);
                            $workflowEnrollment->setRecord($record);
                            $this->entityManager->persist($workflowEnrollment);
                        }

                        if (($index % $batchSize) === 0) {
                            $this->entityManager->flush();
                            $this->entityManager->clear();
                            // We are clearing the entity manager with each batch so we need to pull a fresh workflow object
                            $workflow = $this->workflowRepository->find($workflow->getId());
                        }
                        $index++;

                        unset($record);
                        unset($enrolledWorkflow);
                        gc_collect_cycles();
                    }

                    // flush any remaining records that were created after the last batch update
                    $this->entityManager->flush();
                    break;
            }
        }

        // todo this shouldn't probably be right here as what if some workflows don't require enrollment or a record?
        //  like workflows that aren't property/record based. We might not even need a switch case here if this is just property trigger based
        $output->writeln(['Workflow enrollments completed...', '============', '',]);
    }

    /**
     * This is important for saving memory consumption as standard loops
     * keep leaking memory as each loop iteration variable is stored in memory.
     * Extremely helpful for large data sets and background workers where memory
     * consumption is of utmost priority
     *
     * @param $results
     * @return array|\Generator
     * @see https://www.php.net/manual/en/language.generators.overview.php
     */
    private function recordGenerator($results) {

        if(empty($results['results'])) {
            return [];
        }

        for($i = 0; $i < count($results['results']); $i++) {
            yield $results['results'][$i]['id'];
        }
    }
}