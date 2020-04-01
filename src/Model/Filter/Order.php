<?php

namespace App\Model\Filter;

class Order
{
    use Uid;

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

    public function getQuery(Column $column) {
        return [
            'sql' => sprintf("`%s`.properties->>'$.\"%s\"' %s", $column->getAlias(), $column->getProperty()->getInternalName(), $this->sort),
            'bindings' => []
        ];
    }

    public function getQueryWithBindings(Column $column) {
        $internalName = sprintf('$."%s"', $column->getProperty()->getInternalName());
        return [
            'sql' => sprintf("`%s`.properties->>? %s", $column->getAlias(), $this->sort),
            'bindings' => [$internalName]
        ];
    }
}