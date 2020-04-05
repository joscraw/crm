<?php

namespace App\Command;

use App\Entity\WorkflowEnrollment;
use App\Mailer\WorkflowSendEmailActionMailer;
use App\Message\WorkflowMessage;
use App\Repository\RecordRepository;
use App\Repository\WorkflowEnrollmentRepository;
use App\Repository\WorkflowRepository;
use App\Service\WorkflowProcessor;
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
     * @var WorkflowSendEmailActionMailer
     */
    private $workflowSendEmailActionMailer;

    /**
     * @var WorkflowEnrollmentRepository
     */
    private $workflowEnrollmentRepository;

    protected static $defaultName = 'app:workflow:enrollment';

    /**
     * WorkflowEnrollmentCommand constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param WorkflowRepository $workflowRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     * @param WorkflowSendEmailActionMailer $workflowSendEmailActionMailer
     * @param WorkflowEnrollmentRepository $workflowEnrollmentRepository
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        WorkflowRepository $workflowRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        SerializerInterface $serializer,
        WorkflowSendEmailActionMailer $workflowSendEmailActionMailer,
        WorkflowEnrollmentRepository $workflowEnrollmentRepository
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->workflowRepository = $workflowRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->serializer = $serializer;
        $this->workflowSendEmailActionMailer = $workflowSendEmailActionMailer;
        $this->workflowEnrollmentRepository = $workflowEnrollmentRepository;

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

        $numRecordsEnrolled = 0;

        foreach($workflows as $workflow) {

            $publishedWorkflow = $workflow->getPublishedWorkflow();
            foreach($publishedWorkflow->getTriggers() as $trigger) {
                switch ($trigger->getName()) {
                    case PropertyTrigger::PROPERTY_BASED_TRIGGER:
                        /** @var PropertyTrigger $trigger */
                        $filters = $trigger->getFilters();
                        $json = $this->serializer->serialize($filters, 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'WORKFLOW_ACTION']]);
                        $filters = json_decode($json, true);
                        $results = $this->recordRepository->getTriggerFilterMysqlOnly($filters, $publishedWorkflow->getCustomObject());
                        foreach($results['results'] as $result) {
                            $record = $this->recordRepository->find($result['id']);

                            $enrolledWorkflow = $this->workflowEnrollmentRepository->findOneBy([
                               'record' => $record,
                               'workflow' => $publishedWorkflow,
                            ]);

                            if($enrolledWorkflow) {
                                $output->writeln([sprintf('record %s already enrolled for workflow %s...', $record->getId(), $publishedWorkflow->getId()), '============', '',]);
                                continue;
                            }

                            $workflowEnrollment = new WorkflowEnrollment();
                            $workflowEnrollment->setWorkflow($publishedWorkflow);
                            $workflowEnrollment->setRecord($record);
                            $this->entityManager->persist($workflowEnrollment);
                            $this->entityManager->flush();

                            $this->bus->dispatch(new WorkflowMessage($workflowEnrollment->getId()));
                            $output->writeln([sprintf('workflow enrollment %s added to queue...', $workflowEnrollment->getId()), '============', '',]);
                            $numRecordsEnrolled++;
                        }
                        break;
                }
            }
        }

        $output->writeln([sprintf('%s Records enrolled.', $numRecordsEnrolled), '============', '',]);
    }
}