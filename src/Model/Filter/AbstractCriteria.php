<?php

namespace App\Model\Filter;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AbstractCriteria
{
    /**
     * @var AndCriteria[]
     */
    protected $and;

    /**
     * @var OrCriteria[]
     */
    protected $or;

    public function __construct()
    {
        $this->and = new ArrayCollection();
        $this->or = new ArrayCollection();
    }

    /**
     * @return Collection|AndCriteria[]
     */
    public function getAndCriteria(): Collection
    {
        return $this->and;
    }

    /**
     * @param $andCollection
     * @return AbstractCriteria
     */
    public function setAndCriteria($andCollection): AbstractCriteria
    {
        $this->and = $andCollection;
        return $this;
    }

    public function addAndCriteria(AndCriteria $and): self
    {
        $this->and[] = $and;
        return $this;
    }

    public function removeAndCriteria(AndCriteria $and): self
    {
        if ($this->and->contains($and)) {
            $this->and->removeElement($and);
        }

        return $this;
    }

    /**
     * @return Collection|OrCriteria[]
     */
    public function getOrCriteria(): Collection
    {
        return $this->or;
    }

    /**
     * @param $orCollection
     * @return AbstractCriteria
     */
    public function setOrCriteria($orCollection): AbstractCriteria
    {
        $this->or = $orCollection;
        return $this;
    }

    public function addOrCriteria(OrCriteria $or): self
    {
        $this->or[] = $or;
        return $this;
    }

    public function removeOrCriteria(OrCriteria $or): self
    {
        if ($this->or->contains($or)) {
            $this->or->removeElement($or);
        }

        return $this;
    }
}