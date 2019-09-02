<?php

namespace App\Service;


use App\Entity\Action;
use App\Entity\PropertyTrigger;
use App\Entity\Record;
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
     * RecordListener constructor.
     * @param SerializerInterface $serializer
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param RecordRepository $recordRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SerializerInterface $serializer,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        RecordRepository $recordRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->serializer = $serializer;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->recordRepository = $recordRepository;
        $this->entityManager = $entityManager;
    }

    public function run(Record $record) {

        $objectWorkflowDrafts = $this->objectWorkflowRepository->findBy([
            'customObject' => $record->getCustomObject(),
            'published' => true,
            'draft' => true,
            'paused' => false
        ]);

        foreach($objectWorkflowDrafts as $objectWorkflowDraft) {
            $publishedWorkflow = $objectWorkflowDraft->getPublishedWorkflow();

            $json = $this->serializer->serialize($publishedWorkflow, 'json', ['groups' => ['WORKFLOW', 'TRIGGER', 'WORKFLOW_ACTION']]);
            $publishedWorkflowArray = json_decode($json, true);

            $triggers = $publishedWorkflowArray['triggers'];
            foreach($triggers as $trigger) {

                switch ($trigger['name']) {
                    case PropertyTrigger::PROPERTY_BASED_TRIGGER:
                        $filters = $trigger['filters'];

                        $results = $this->recordRepository->getTriggerFilterMysqlOnly($filters, $objectWorkflowDraft->getCustomObject());

                        foreach($results['results'] as $result) {

                            $record = $this->recordRepository->find($result['id']);

                            // get the actions
                            $actions = $publishedWorkflowArray['actions'];
                            foreach($actions as $action) {
                                switch ($action['name']) {
                                    case Action::SET_PROPERTY_VALUE_ACTION:
                                        $properties = $record->getProperties();
                                        $properties[$action['property']['internalName']] = $action['value'];
                                        $record->setProperties($properties);
                                        $this->entityManager->persist($record);
                                        $this->entityManager->flush();
                                        break;
                                }
                            }
                        }
                        break;
                }
            }
        }
    }

}