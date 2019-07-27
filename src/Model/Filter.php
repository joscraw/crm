<?php

namespace App\Model;

use App\Entity\Property;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class Filter
 * @package App\Model
 */
class Filter extends Property
{
    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected $operator;

    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected $value;

    /**
     * @Groups({"TRIGGER"})
     * @var array
     */
    protected $joins = [];

    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected $referencedFilterPath;

    /**
     * @Groups({"TRIGGER"})
     * @var array
     */
    protected $andFilters = [];

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
    }

    /**
     * @return string
     */
    public function getReferencedFilterPath()
    {
        return $this->referencedFilterPath;
    }

    /**
     * @param string $referencedFilterPath
     */
    public function setReferencedFilterPath(string $referencedFilterPath)
    {
        $this->referencedFilterPath = $referencedFilterPath;
    }

    /**
     * @return array
     */
    public function getAndFilters()
    {
        return $this->andFilters;
    }

    /**
     * @param $andFilters
     */
    public function setAndFilters($andFilters)
    {
        $this->andFilters = $andFilters;
    }
}