<?php

namespace App\Command;

use App\Entity\WorkflowAction;
use App\Entity\WorkflowEnrollment;
use App\Message\WorkflowActionMessage;
use App\Message\WorkflowPropertyUpdateActionMessage;
use App\Repository\WorkflowEnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class WorkflowEnrollmentProcessorCommand
 * @package App\Command
 */
class WorkflowEnrollmentProcessorCommand extends Command
{

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

    protected static $defaultName = 'app:workflow:enrollment:processor';

    /**
     * WorkflowEnrollmentProcessorCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     * @param WorkflowEnrollmentRepository $workflowEnrollmentRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        SerializerInterface $serializer,
        WorkflowEnrollmentRepository $workflowEnrollmentRepository
    ) {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->serializer = $serializer;
        $this->workflowEnrollmentRepository = $workflowEnrollmentRepository;

        parent::__construct();
    }


    protected function configure()
    {
        $this->setDescription('Processes any workflow enrollments and passes them off to the queue.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryBuilder = $this->workflowEnrollmentRepository->findAllQueryBuilder();
        $query = $queryBuilder->getQuery();
        $iterableResult = $query->iterate();

        foreach ($iterableResult as $row) {
            /** @var WorkflowEnrollment $workflowEnrollment */
            $workflowEnrollment = $row[0];
            $workflow = $workflowEnrollment->getWorkflow();
            $record = $workflowEnrollment->getRecord();

            if(!$workflow || !$record) {
                continue;
            }

            // Let's make sure that the record enrolled still meets the criteria for being run through the workflow
            if($workflow->shouldInvoke($this->serializer, $this->entityManager, $record)) {
                // We only want to fire the first action on a given workflow as each
                // sequential action will get fired from inside the next workflow action handler
                /** @var WorkflowAction $workflowAction */
                $workflowAction = $workflow->getFirstActionInSequence();

                // I don't know why a workflow wouldn't have at least one action but
                // let's just play it safe here and check for it.
                if(!$workflowAction) {
                    continue;
                }

                /** @var WorkflowActionMessage $message */
                $message = $workflowAction->getHandlerMessage();

                switch ($message) {
                    case WorkflowPropertyUpdateActionMessage::class:

                        $input = [
                            'recordId' => $record->getId(),
                            'recordProperties' => $record->getProperties()
                        ];

                        /** @var WorkflowPropertyUpdateActionMessage $message */
                        $message = new $message($workflowAction->getId(), $record->getId());
                        $message->addInput($input);
                        break;
                }

                $this->bus->dispatch($message);
            }

            // detach from Doctrine, so that it can be Garbage-Collected immediately
            $this->entityManager->clear();
        }
    }
}