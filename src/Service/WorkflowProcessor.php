<?php

namespace App\Service;


use App\Entity\Action;
use App\Entity\PropertyTrigger;
use App\Entity\Record;
use App\Entity\SendEmailAction;
use App\Entity\SetPropertyValueAction;
use App\Entity\Workflow;
use App\Mailer\WorkflowSendEmailActionMailer;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WorkflowProcessor
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var WorkflowRepository
     */
    private $workflowRepository;

    /**
     * @var ObjectWorkflowRepository
     */
    private $objectWorkflowRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var WorkflowSendEmailActionMailer
     */
    private $workflowSendEmailActionMailer;

    /**
     * WorkflowProcessor constructor.
     * @param SerializerInterface $serializer
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     * @param WorkflowSendEmailActionMailer $workflowSendEmailActionMailer
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager,
        WorkflowSendEmailActionMailer $workflowSendEmailActionMailer
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
        $this->workflowSendEmailActionMailer = $workflowSendEmailActionMailer;
    }

    public function run(Workflow $workflow) {

        $publishedWorkflow = $workflow->getPublishedWorkflow();
        foreach($publishedWorkflow->getTriggers() as $trigger) {
            switch ($trigger->getName()) {
                case PropertyTrigger::PROPERTY_BASED_TRIGGER:
                    /** @var PropertyTrigger $trigger */
                    $filters = $trigger->getFilters();
                    $json = $this->serializer->serialize($filters, 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'WORKFLOW_ACTION']]);
                    $filters = json_decode($json, true);
                    $results = $this->recordRepository->getTriggerFilterMysqlOnly($filters, $workflow->getCustomObject());
                    foreach($results['results'] as $result) {
                        $record = $this->recordRepository->find($result['id']);



                        foreach($publishedWorkflow->getActions() as $action) {
                            switch ($action->getName()) {
                                case Action::SET_PROPERTY_VALUE_ACTION:
                                    /** @var SetPropertyValueAction $action */
                                    $properties = $record->getProperties();
                                    $properties[$action->getProperty()->getInternalName()] = $action->getValue();
                                    $record->setProperties($properties);
                                    $this->entityManager->persist($record);
                                    $this->entityManager->flush();
                                    break;
                                case Action::SEND_EMAIL_ACTION:
                                    /** @var SendEmailAction $action */
                                    $mergeTags = $action->getMergeTags();
                                    $results = $this->recordRepository->getPropertiesFromMergeTagsByRecord($mergeTags, $record);

                                    foreach($results['results'] as $record) {
                                        $this->workflowSendEmailActionMailer->send(
                                            $action->getMergedSubject($record),
                                            $action->getMergedToAddresses($record),
                                            $action->getMergedBody($record)
                                        );
                                    }
                                    break;
                            }
                        }
                    }
                    break;
            }
        }
    }

}