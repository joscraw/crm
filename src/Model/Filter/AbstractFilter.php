<?php

namespace App\Model\Filter;


use App\Utils\ArrayHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AbstractFilter
{
    use RandomStringGenerator;
    use ArrayHelper;

    /**
     * @var Filter[]
     */
    protected $filters;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * @var Join[]
     */
    protected $joins;

    /**
     * @var string
     */
    protected $alias;

    public function __construct()
    {
        $this->columns = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->joins = new ArrayCollection();
    }

    /**
     * @return Collection|Column[]
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function addColumn(Column $column): self
    {
        $this->columns[] = $column;
        return $this;
    }

    public function removeColumn(Column $column): self
    {
        if ($this->columns->contains($column)) {
            $this->columns->removeElement($column);
        }

        return $this;
    }

    /**
     * @return Collection|Filter[]
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        if ($this->filters->contains($filter)) {
            $this->filters->removeElement($filter);
        }

        return $this;
    }

    /**
     * @param $joins Collection|Join[]
     * @return AbstractFilter
     */
    public function setJoins($joins): AbstractFilter
    {
        $this->joins = $joins;

        return $this;
    }

    /**
     * @return Collection|Join[]
     */
    public function getJoins(): Collection
    {
        return $this->joins;
    }

    public function addJoin(Join $join): self
    {
        $this->joins[] = $join;
        return $this;
    }

    public function removeJoin(Join $join): self
    {
        if ($this->joins->contains($join)) {
            $this->joins->removeElement($join);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @param string $uid
     * @return Filter|bool
     */
    public function getFilterByUid(string $uid) {

        foreach($this->getFilters() as $filter) {
            if($filter->getUid() === $uid) {
                return $filter;
            }
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            if($filter = $join->getFilterByUid($uid)) {
                return $filter;
            }
        }

        return false;
    }

    /**
     * @param string $uid
     * @return Column|bool
     */
    public function getColumnByUid(string $uid) {

        foreach($this->getColumns() as $column) {
            if($column->getUid() === $uid) {
                return $column;
            }
        }

        /** @var Join $join */
        foreach($this->joins as $join) {
            if($column = $join->getColumnByUid($uid)) {
                return $column;
            }
        }

        return false;
    }
}