<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Validator\Constraints as CustomAssert;

/**
 * @CustomAssert\RecordProperty()
 * @ORM\Entity(repositoryClass="App\Repository\RecordRepository")
 * @ORM\EntityListeners({"App\Entity\Listener\RecordListener"})
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RecordDuplicate", mappedBy="conflictingRecord", orphanRemoval=true)
     */
    private $recordDuplicates;

    public function __construct()
    {
        $this->workflowEnrollments = new ArrayCollection();
        $this->recordDuplicates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __get($key) {
        $properties = $this->getProperties();
        return array_key_exists($key, $properties) ? $properties[$key] : null;
    }

    public function __set($key, $value) {
        $properties = $this->getProperties();
        $properties[$key] = $value;
        $this->setProperties($properties);
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

    /**
     * @return Collection|RecordDuplicate[]
     */
    public function getRecordDuplicates(): Collection
    {
        return $this->recordDuplicates;
    }

    public function addRecordDuplicate(RecordDuplicate $recordDuplicate): self
    {
        if (!$this->recordDuplicates->contains($recordDuplicate)) {
            $this->recordDuplicates[] = $recordDuplicate;
            $recordDuplicate->setConflictingRecord($this);
        }

        return $this;
    }

    public function removeRecordDuplicate(RecordDuplicate $recordDuplicate): self
    {
        if ($this->recordDuplicates->contains($recordDuplicate)) {
            $this->recordDuplicates->removeElement($recordDuplicate);
            // set the owning side to null (unless already changed)
            if ($recordDuplicate->getConflictingRecord() === $this) {
                $recordDuplicate->setConflictingRecord(null);
            }
        }

        return $this;
    }
}
