<?php

namespace App\Model\Filter;

use App\Entity\Property;

class Order
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * ASC/DESC
     * @var string
     */
    protected $sort;

    /**
     * @var integer
     */
    protected $priority = 0;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property): void
    {
        $this->property = $property;
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
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     */
    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getQuery() {

        $orderQuery = <<<HERE
`%s`.properties->>'$."%s"' %s
HERE;
        return sprintf($orderQuery, $this->alias, $this->property->getInternalName(), $this->sort);
    }
}