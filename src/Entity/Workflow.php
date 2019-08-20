<?php

namespace App\Entity;

use App\Model\AbstractTrigger;
use App\Model\AbstractAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

// @ORM\EntityListeners({"App\EntityListener\WorkflowListener"})

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"objectWorkflow" = "ObjectWorkflow"})
 */
abstract class Workflow
{
    const OBJECT_WORKFLOW = 'OBJECT_WORKFLOW';

    public static $types = [
        [
            'name' => self::OBJECT_WORKFLOW,
            'label' => 'Object Workflow'
        ]
    ];

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="workflows")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $portal;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="string", length=255)
     */
    protected $uid;


    /**
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function setWorkflowName()
    {
        if(empty($this->name)) {
            $this->name = sprintf('New workflow (%s)', date("M j, Y g:i:s A"));
        }
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $published = false;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="json", nullable=true)
     */
    protected $draft = [];

    /**
     * @Groups({"WORKFLOW"})
     * @var array
     */
    protected $actions = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(?Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getDraft(): ?array
    {
        return $this->draft;
    }

    public function setDraft(?array $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
