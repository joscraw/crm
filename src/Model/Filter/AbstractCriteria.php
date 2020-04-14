<?php

namespace App\Model\Filter;

use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AbstractCriteria
{
    use Uid;

    /**
     * @var AndCriteria[]
     */
    protected $and;

    /**
     * @var OrCriteria[]
     */
    protected $or;

    /**
     * AbstractCriteria constructor.
     * @param ArrayCollection $andCriteria
     * @param ArrayCollection $orCriteria
     */
    public function __construct(ArrayCollection $andCriteria = null, ArrayCollection $orCriteria = null)
    {
        $this->and = new ArrayCollection();
        $this->or = new ArrayCollection();

        if($andCriteria instanceof ArrayCollection && $andCriteria->count() > 0) {
            $this->and = $andCriteria;
        }

        if($orCriteria instanceof ArrayCollection && $orCriteria->count() > 0) {
            $this->or = $orCriteria;
        }
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

    public function generateFilterCriteria(FilterData $filterData) {

        // Uids are not required for defining Criteria. Criteria can simply
        // be  used for grouping and do NOT necessarily have to have a filter attached
        if($this->getUid()) {
            $filterData->filterCriteriaParts[] = $this->getQuery();
        }

        if($this->or->count() > 0 && $this->getUid()) {
            $filterData->filterCriteriaParts[] = " OR \n";
            $filterData->filterCriteriaParts[] = ' ( ';
        }

        $i = 1;
        foreach($this->or as $orCriteria) {
            $orCriteria->generateFilterCriteria($filterData);

            if($i !== $this->or->count()) {
                $filterData->filterCriteriaParts[] = " OR \n";
            }
            $i++;
        }

        if($this->or->count() > 0 && $this->getUid()) {
            $filterData->filterCriteriaParts[] = ' ) ';
        }

        if($this->and->count() > 0 && $this->getUid()) {
            $filterData->filterCriteriaParts[] = " AND \n";
            $filterData->filterCriteriaParts[] = ' ( ';
        }

        $i = 1;
        foreach($this->and as $andCriteria) {
            $andCriteria->generateFilterCriteria($filterData);

            if($i !== $this->and->count()) {
                $filterData->filterCriteriaParts[] = " AND \n";
            }
            $i++;
        }

        if($this->and->count() > 0 && $this->getUid()) {
            $filterData->filterCriteriaParts[] = ' ) ';
        }
    }

    /**
     * @return string
     */
    public function getQuery() {

        return $this->getUid();

    }

    public function getAllUids($uids = []) {
        /** @var AbstractCriteria $criteria */
        foreach(array_merge($this->and->toArray(), $this->or->toArray()) as $criteria) {
            $uids[] = $criteria->getUid();
            $uids = $criteria->getAllUids($uids);
        }
        return $uids;
    }
}