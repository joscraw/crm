<?php

namespace App\MessageHandler;

use App\Entity\Action;
use App\Entity\SendEmailAction;
use App\Entity\SetPropertyValueAction;
use App\Mailer\ResetPasswordMailer;
use App\Mailer\WorkflowSendEmailActionMailer;
use App\Repository\ObjectWorkflowRepository;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use App\Repository\WorkflowEnrollmentRepository;
use App\Repository\WorkflowRepository;
use App\Service\WorkflowProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\WorkflowMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

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

    /**
     * WorkflowHandler constructor.
     * @param WorkflowProcessor $workflowProcessor
     * @param RecordRepository $recordRepository
     * @param UserRepository $userRepository
     * @param WorkflowRepository $workflowRepository
     * @param ObjectWorkflowRepository $objectWorkflowRepository
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface $bus
     * @param SerializerInterface $serializer
     * @param WorkflowSendEmailActionMailer $workflowSendEmailActionMailer
     * @param WorkflowEnrollmentRepository $workflowEnrollmentRepository
     */
    public function __construct(
        WorkflowProcessor $workflowProcessor,
        RecordRepository $recordRepository,
        UserRepository $userRepository,
        WorkflowRepository $workflowRepository,
        ObjectWorkflowRepository $objectWorkflowRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        SerializerInterface $serializer,
        WorkflowSendEmailActionMailer $workflowSendEmailActionMailer,
        WorkflowEnrollmentRepository $workflowEnrollmentRepository
    ) {
        $this->workflowProcessor = $workflowProcessor;
        $this->recordRepository = $recordRepository;
        $this->userRepository = $userRepository;
        $this->workflowRepository = $workflowRepository;
        $this->objectWorkflowRepository = $objectWorkflowRepository;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->serializer = $serializer;
        $this->workflowSendEmailActionMailer = $workflowSendEmailActionMailer;
        $this->workflowEnrollmentRepository = $workflowEnrollmentRepository;
    }

    public function __invoke(WorkflowMessage $message)
    {
        $workflowEnrollmentId = $message->getContent();
        $workflowEnrollment = $this->workflowEnrollmentRepository->find($workflowEnrollmentId);

        if(!$workflowEnrollment) {
            return;
        }

        $publishedWorkflow = $workflowEnrollment->getWorkflow();
        $record = $workflowEnrollment->getRecord();

        foreach($publishedWorkflow->getActions() as $action) {
            switch ($action->getName()) {
                case Action::SET_PROPERTY_VALUE_ACTION:
                    /** @var SetPropertyValueAction $action */
                    $joinPath = $action->getJoins();
                    array_shift($joinPath);
                    $joinPath[] = $action->getProperty()->getInternalName();
                    $mergeTag = implode(".", $joinPath);

                    $results = $this->recordRepository->getRecordByPropertyDotAnnotation($mergeTag, $record);
                    foreach($results['results'] as $result) {
                        $recordToModify = $this->recordRepository->find($result['id']);
                        $properties = $recordToModify->getProperties();

                        switch ($action->getOperator()) {
                            case 'INCREMENT_BY':
                                if(!empty($properties[$action->getProperty()->getInternalName()])) {
                                    $properties[$action->getProperty()->getInternalName()] = (string) ($properties[$action->getProperty()->getInternalName()] + $action->getValue());
                                } else {
                                    $properties[$action->getProperty()->getInternalName()] = "1";
                                }
                                break;
                            case 'DECREMENT_BY':
                                if(!empty($properties[$action->getProperty()->getInternalName()])) {
                                    $properties[$action->getProperty()->getInternalName()] = (string) ($properties[$action->getProperty()->getInternalName()] - $action->getValue());
                                } else {
                                    $properties[$action->getProperty()->getInternalName()] = "-1";
                                }
                                break;
                            default:
                                $properties[$action->getProperty()->getInternalName()] = $action->getValue();
                                break;
                        }


                        $recordToModify->setProperties($properties);
                        $this->entityManager->persist($recordToModify);
                    }
                    $this->entityManager->flush();
                    break;
                case Action::SEND_EMAIL_ACTION:
                    /** @var SendEmailAction $action */
                    $mergeTags = $action->getMergeTags();
                    $results = $this->recordRepository->getPropertiesFromMergeTagsByRecord($mergeTags, $record);
                    foreach($results['results'] as $result) {
                        $this->workflowSendEmailActionMailer->send(
                            $action->getMergedSubject($result),
                            $action->getMergedToAddresses($result),
                            $action->getMergedBody($result)
                        );
                    }
                    break;
            }
        }

        $workflowEnrollment->setIsCompleted(true);
        $this->entityManager->flush();

        echo sprintf("Workflow %s completed for record %s", $publishedWorkflow->getId(), $record->getId());
    }
}