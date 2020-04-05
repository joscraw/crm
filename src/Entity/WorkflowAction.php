<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowActionRepository")
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *     "workflowPropertyUpdateAction" = "WorkflowPropertyUpdateAction",
 *     "workflowSendEmailAction" = "WorkflowSendEmailAction"
 *     })
 *
 *
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "workflow-property-update-action"="App\Entity\WorkflowPropertyUpdateAction",
 *    "workflow-send-email-action"="App\Entity\WorkflowSendEmailAction"
 * })
 */
abstract class WorkflowAction
{
    const WORKFLOW_PROPERTY_UPDATE_ACTION = 'workflow-property-update-action';
    const WORKFLOW_SEND_EMAIL_ACTION = 'workflow-send-email-action';

    /**
     * @var string
     */
    protected static $name = 'workflow-action';

    /**
     * @var string
     */
    protected static $description = 'workflow description';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Workflow", inversedBy="workflowActions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    public static function getName() {
        return static::$name;
    }

    public static function getDescription() {
        return static::$description;
    }

    /**
     * @param string $name
     */
    public static function setName(string $name): void
    {
        self::$name = $name;
    }

    /**
     * @param string $description
     */
    public static function setDescription(string $description): void
    {
        self::$description = $description;
    }
}
