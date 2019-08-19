<?php

namespace App\EntityListener;

use App\Entity\CustomObject;
use App\Entity\Property;
use App\Entity\Workflow;
use App\Model\AbstractAction;
use App\Model\AbstractField;
use App\Model\AbstractTrigger;
use App\Model\CustomObjectField;
use App\Repository\PropertyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;



use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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