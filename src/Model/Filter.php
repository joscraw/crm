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
     * @var array
     */
    protected $referencedFilterPaths = [];

    /**
     * @Groups({"TRIGGER"})
     * @var array
     */
    protected $orFilters = [];


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
     * @return array
     */
    public function getReferencedFilterPaths()
    {
        return $this->referencedFilterPaths;
    }

    /**
     * @param $referencedFilterPaths
     */
    public function setReferencedFilterPaths($referencedFilterPaths)
    {
        $this->referencedFilterPaths = $referencedFilterPaths;
    }

    /**
     * @return array
     */
    public function getOrFilters()
    {
        return $this->orFilters;
    }

    /**
     * @param $orFilters
     */
    public function setOrFilters($orFilters)
    {
        $this->orFilters = $orFilters;
    }
}