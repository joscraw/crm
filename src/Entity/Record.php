<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecordRepository")
 */
class Record
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="records")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @ORM\Column(type="json")
     */
    private $properties = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WorkflowEnrollment", mappedBy="record", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $workflowEnrollments;

    public function __construct()
    {
        $this->workflowEnrollments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function setProperties($properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return Collection|WorkflowEnrollment[]
     */
    public function getWorkflowEnrollments(): Collection
    {
        return $this->workflowEnrollments;
    }

    public function addWorkflowEnrollment(WorkflowEnrollment $workflowEnrollment): self
    {
        if (!$this->workflowEnrollments->contains($workflowEnrollment)) {
            $this->workflowEnrollments[] = $workflowEnrollment;
            $workflowEnrollment->setRecord($this);
        }

        return $this;
    }

    public function removeWorkflowEnrollment(WorkflowEnrollment $workflowEnrollment): self
    {
        if ($this->workflowEnrollments->contains($workflowEnrollment)) {
            $this->workflowEnrollments->removeElement($workflowEnrollment);
            // set the owning side to null (unless already changed)
            if ($workflowEnrollment->getRecord() === $this) {
                $workflowEnrollment->setRecord(null);
            }
        }

        return $this;
    }
}
