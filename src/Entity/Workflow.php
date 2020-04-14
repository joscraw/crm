<?php

namespace App\Entity;

use App\Model\Filter\AndCriteria;
use App\Model\Filter\FilterData;
use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use App\Model\Filter\Filter;


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

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $workflowTrigger;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\WorkflowInput", mappedBy="workflow", orphanRemoval=true)
     */
    private $workflowInputs;

    public function __construct()
    {
        $this->workflowEnrollments = new ArrayCollection();
        $this->workflowActions = new ArrayCollection();
        $this->workflowInputs = new ArrayCollection();
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

    public function shouldInvoke(SerializerInterface $serializer, EntityManagerInterface $entityManager, Record $record) {
        /** @var FilterData $filterData */
        $filterData = $serializer->deserialize(json_encode($this->getFilterData()), FilterData::class, 'json');
        $filterData->setCountOnly(true);
        $property = $entityManager->getRepository(Property::class)->findOneByInternalNameAndCustomObject('id', $record->getCustomObject());
        $uid = $this->generateRandomCharacters(5);
        $filter = new Filter();
        $filter->setProperty($property);
        $filter->setUid($uid);
        $filter->setOperator(Filter::EQ);
        $filter->setValue($record->getId());
        $filterData->getFilterCriteria()->addAndCriteria(new AndCriteria($uid));
        $filterData->addFilter($filter);

        // todo maybe we should fix this and allow easier record searching
        //  we should add the addRecord logic to the FilterData API incase IDs don't always
        //  get added to the records
        // does this api not allow to add a record? I'm not sure.
        /*$filterData->addRecord($record->getId());*/
        $results = $filterData->runQuery($entityManager);
        return $results['count'] > 0;
    }

    public function query(SerializerInterface $serializer, EntityManagerInterface $entityManager) {
        /** @var FilterData $filterData */
        $filterData = $serializer->deserialize(json_encode($this->getFilterData()), FilterData::class, 'json');
        $results = $filterData->runQuery($entityManager);
        return $results;
    }

    /**
     * @return array
     */
    public function getFirstActionInSequence() {
        $sort = new Criteria(null, ['sequence' => Criteria::ASC]);
        return $this->workflowActions->matching($sort)->first();
    }

    /**
     * @return array
     */
    public function getActionsInSequence() {
        $sort = new Criteria(null, ['sequence' => Criteria::ASC]);
        return $this->workflowActions->matching($sort);
    }

    /**
     * @param WorkflowAction $workflowAction
     * @return array
     */
    public function getNextActionInSequence(WorkflowAction $workflowAction) {

        $workflowActions = $this->workflowActions->filter(function(WorkflowAction $action) use($workflowAction) {
            return $action->getSequence() === ($workflowAction->getSequence() + 1);
        });

        if($workflowActions->count() > 0) {
            return $workflowActions->first();
        }

        return null;
    }

    public function getWorkflowTrigger(): ?string
    {
        return $this->workflowTrigger;
    }

    public function setWorkflowTrigger(string $workflowTrigger): self
    {
        $this->workflowTrigger = $workflowTrigger;

        return $this;
    }

    /**
     * @return Collection|WorkflowInput[]
     */
    public function getWorkflowInputs(): Collection
    {
        return $this->workflowInputs;
    }

    public function addWorkflowInput(WorkflowInput $workflowInput): self
    {
        if (!$this->workflowInputs->contains($workflowInput)) {
            $this->workflowInputs[] = $workflowInput;
            $workflowInput->setWorkflow($this);
        }

        return $this;
    }

    public function removeWorkflowInput(WorkflowInput $workflowInput): self
    {
        if ($this->workflowInputs->contains($workflowInput)) {
            $this->workflowInputs->removeElement($workflowInput);
            // set the owning side to null (unless already changed)
            if ($workflowInput->getWorkflow() === $this) {
                $workflowInput->setWorkflow(null);
            }
        }

        return $this;
    }
}
