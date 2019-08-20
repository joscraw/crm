<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ObjectWorkflowRepository")
 */
class ObjectWorkflow extends Workflow
{
    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    public static $nameDisc = 'objectWorkflow';

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="objectWorkflows")
     */
    protected $customObject;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\OneToMany(targetEntity="App\Entity\PropertyTrigger", mappedBy="objectWorkflow")
     */
    protected $triggers;

    public function __construct()
    {
        $this->triggers = new ArrayCollection();
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

    /**
     * @return string
     */
    public static function getNameDisc()
    {
        return self::$nameDisc;
    }

    /**
     * @return Collection|PropertyTrigger[]
     */
    public function getTriggers(): Collection
    {
        return $this->triggers;
    }

    public function addTrigger(PropertyTrigger $trigger): self
    {
        if (!$this->triggers->contains($trigger)) {
            $this->triggers[] = $trigger;
            $trigger->setObjectWorkflow($this);
        }

        return $this;
    }

    public function removeTrigger(PropertyTrigger $trigger): self
    {
        if ($this->triggers->contains($trigger)) {
            $this->triggers->removeElement($trigger);
            // set the owning side to null (unless already changed)
            if ($trigger->getObjectWorkflow() === $this) {
                $trigger->setObjectWorkflow(null);
            }
        }

        return $this;
    }
}
