<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PropertyTriggerRepository")
 */
class PropertyTrigger extends Trigger
{
    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    protected $name = 'property_based_trigger';

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="string", length=255)
     */
    protected $description = 'Property based trigger.';

    /**
     * @var TriggerFilter|[]
     * @Groups({"TRIGGER"})
     * @ORM\OneToMany(targetEntity="App\Entity\TriggerFilter", mappedBy="propertyTrigger", cascade={"persist", "remove"})
     */
    protected $filters = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ObjectWorkflow", inversedBy="triggers")
     */
    private $objectWorkflow;

    public function __construct()
    {
        $this->filters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|TriggerFilter[]
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(TriggerFilter $filter): self
    {
        if (!$this->filters->contains($filter)) {
            $this->filters[] = $filter;
            $filter->setPropertyTrigger($this);
        }

        return $this;
    }

    public function removeFilter(TriggerFilter $filter): self
    {
        if ($this->filters->contains($filter)) {
            $this->filters->removeElement($filter);
            // set the owning side to null (unless already changed)
            if ($filter->getPropertyTrigger() === $this) {
                $filter->setPropertyTrigger(null);
            }
        }

        return $this;
    }

    public function setFilters($filters) {
        $this->filters = $filters;

        return $this;
    }

    public function getObjectWorkflow(): ?ObjectWorkflow
    {
        return $this->objectWorkflow;
    }

    public function setObjectWorkflow(?ObjectWorkflow $objectWorkflow): self
    {
        $this->objectWorkflow = $objectWorkflow;

        return $this;
    }


}
