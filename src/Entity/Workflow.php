<?php

namespace App\Entity;

use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Workflow
{
    use TimestampableEntity;
    use RandomStringGenerator;

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
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="boolean")
     */
    protected $paused = true;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $filterData = [];

    /**
     * todo possibly refactor into it's own class in the future....
     *
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $triggers = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WorkflowEnrollment", mappedBy="workflow", orphanRemoval=true)
     */
    private $workflowEnrollments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WorkflowAction", mappedBy="workflow", orphanRemoval=true, cascade={"persist"})
     */
    private $workflowActions;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="workflows")
     */
    private $customObject;

    public function __construct()
    {
        $this->workflowEnrollments = new ArrayCollection();
        $this->workflowActions = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function setNameOnPrePersist()
    {
        if(empty($this->name)) {
            $this->name = sprintf('New workflow (%s)', date("M j, Y g:i:s A"));
        }
    }

    /**
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function setUidOnPrePersist()
    {
        if(empty($this->uid)) {
            $this->uid = $this->generateRandomString(40);
        }
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

    public function getFilterData(): ?array
    {
        return $this->filterData;
    }

    public function setFilterData(?array $filterData): self
    {
        $this->filterData = $filterData;

        return $this;
    }

    public function getTriggers(): ?array
    {
        return $this->triggers;
    }

    public function setTriggers(?array $triggers): self
    {
        $this->triggers = $triggers;

        return $this;
    }

    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
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

    public function getPaused(): ?bool
    {
        return $this->paused;
    }

    public function setPaused(bool $paused): self
    {
        $this->paused = $paused;

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
            $workflowEnrollment->setWorkflow($this);
        }

        return $this;
    }

    public function removeWorkflowEnrollment(WorkflowEnrollment $workflowEnrollment): self
    {
        if ($this->workflowEnrollments->contains($workflowEnrollment)) {
            $this->workflowEnrollments->removeElement($workflowEnrollment);
            // set the owning side to null (unless already changed)
            if ($workflowEnrollment->getWorkflow() === $this) {
                $workflowEnrollment->setWorkflow(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|WorkflowAction[]
     */
    public function getWorkflowActions(): Collection
    {
        return $this->workflowActions;
    }

    public function addWorkflowAction(WorkflowAction $workflowAction): self
    {
        if (!$this->workflowActions->contains($workflowAction)) {
            $this->workflowActions[] = $workflowAction;
            $workflowAction->setWorkflow($this);
        }

        return $this;
    }

    public function removeWorkflowAction(WorkflowAction $workflowAction): self
    {
        if ($this->workflowActions->contains($workflowAction)) {
            $this->workflowActions->removeElement($workflowAction);
            // set the owning side to null (unless already changed)
            if ($workflowAction->getWorkflow() === $this) {
                $workflowAction->setWorkflow(null);
            }
        }

        return $this;
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
}
