<?php

namespace App\EntityListener;

use App\Entity\Workflow;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class WorkflowListener
 * @package App\Entity\EntityListener
 */
class WorkflowListener
{

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(SerializerInterface $serializer,  EntityManagerInterface $entityManager)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Serialize the field property of the Property entity
     *
     * @param Workflow $workflow
     */
    private function serializePropertyField(Workflow $workflow)
    {
      /*  $triggers = $workflow->getTriggers();
        $data = $this->serializer->serialize($triggers, 'json', ['groups' => ['TRIGGER', 'SELECTABLE_PROPERTIES']]);
        $workflow->setTriggers(json_decode($data, true));

        $actions = $workflow->getActions();
        $data = $this->serializer->serialize($actions, 'json', ['groups' => ['WORKFLOW_ACTION', 'SELECTABLE_PROPERTIES']]);
        $workflow->setActions(json_decode($data, true));*/
    }

    /**
     * Deserialize the field property for the Property entity
     *
     * @param Workflow $workflow
     */
    private function deserializePropertyField(Workflow $workflow)
    {
       /* $triggers = [];
        foreach($workflow->getTriggers() as $key => $trigger) {
            $triggers[$key] = $this->serializer->deserialize(json_encode($trigger, true), AbstractTrigger::class, 'json');
        }
        $workflow->setTriggers($triggers);

        $actions = [];
        foreach($workflow->getActions() as $key => $action) {
            $actions[$key] = $this->serializer->deserialize(json_encode($action, true), AbstractAction::class, 'json');
        }
        $workflow->setActions($actions);*/
    }

    /**
     * Serialize the content property before persisting
     *
     * @param Workflow $workflow
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Workflow $workflow, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($workflow);

    }

    /**
     * Deserialize the content property after loading
     *
     * @param Workflow $workflow
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Workflow $workflow, LifecycleEventArgs $args)
    {
        $this->deserializePropertyField($workflow);
    }

    /**
     * Serialize the content again if it gets updated
     *
     * @param Workflow $workflow
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(Workflow $workflow, LifecycleEventArgs $args)
    {
        $this->serializePropertyField($workflow);
    }
}