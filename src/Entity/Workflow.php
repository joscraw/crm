<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\EntityListeners({"App\EntityListener\WorkflowListener"})
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowRepository")
 */
class Workflow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="workflows")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uid;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WorkflowTrigger", mappedBy="workflow", orphanRemoval=true, cascade={"persist"})
     */
    private $workflowTriggers;

    public function __construct()
    {
        $this->workflowTriggers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
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

    /**
     * @return Collection|WorkflowTrigger[]
     */
    public function getWorkflowTriggers(): Collection
    {
        return $this->workflowTriggers;
    }

    public function addWorkflowTrigger(WorkflowTrigger $workflowTrigger): self
    {
        if (!$this->workflowTriggers->contains($workflowTrigger)) {
            $this->workflowTriggers[] = $workflowTrigger;
            $workflowTrigger->setWorkflow($this);
        }

        return $this;
    }

    public function removeWorkflowTrigger(WorkflowTrigger $workflowTrigger): self
    {
        if ($this->workflowTriggers->contains($workflowTrigger)) {
            $this->workflowTriggers->removeElement($workflowTrigger);
            // set the owning side to null (unless already changed)
            if ($workflowTrigger->getWorkflow() === $this) {
                $workflowTrigger->setWorkflow(null);
            }
        }

        return $this;
    }

    public function clearWorkflowTriggers() {
        $this->workflowTriggers = new ArrayCollection();
    }
}
