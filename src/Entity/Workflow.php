<?php

namespace App\Entity;

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
     * @ORM\OneToMany(targetEntity="App\Entity\Trigger", mappedBy="workflow")
     */
    private $triggers;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\OneToMany(targetEntity="App\Entity\Action", mappedBy="workflow")
     */
    private $actions;

    public function __construct()
    {
        $this->triggers = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

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
    
    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * @return Collection|Trigger[]
     */
    public function getTriggers(): Collection
    {
        return $this->triggers;
    }

    public function addTrigger(Trigger $trigger): self
    {
        if (!$this->triggers->contains($trigger)) {
            $this->triggers[] = $trigger;
            $trigger->setWorkflow($this);
        }

        return $this;
    }

    public function removeTrigger(Trigger $trigger): self
    {
        if ($this->triggers->contains($trigger)) {
            $this->triggers->removeElement($trigger);
            // set the owning side to null (unless already changed)
            if ($trigger->getWorkflow() === $this) {
                $trigger->setWorkflow(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setWorkflow($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            // set the owning side to null (unless already changed)
            if ($action->getWorkflow() === $this) {
                $action->setWorkflow(null);
            }
        }

        return $this;
    }
}
