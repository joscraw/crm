<?php

namespace App\Model\Filter;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AbstractFilter
{
    /**
     * @var Filter[]
     */
    protected $orFilters;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * @var Join[]
     */
    protected $joins;

    /**
     * @var Order[]
     */
    protected $orders;

    /**
     * @var string
     */
    protected $alias;

    public function __construct()
    {
        $this->columns = new ArrayCollection();
        $this->orFilters = new ArrayCollection();
        $this->joins = new ArrayCollection();
        $this->orders = new ArrayCollection();
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
    public function getOrFilters(): Collection
    {
        return $this->orFilters;
    }

    public function addOrFilter(Filter $orFilter): self
    {
        $this->orFilters[] = $orFilter;
        return $this;
    }

    public function removeOrFilter(Filter $orFilter): self
    {
        if ($this->orFilters->contains($orFilter)) {
            $this->orFilters->removeElement($orFilter);
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
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        $this->orders[] = $order;
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
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
}