<?php

namespace App\Model;

use App\Entity\CustomObject;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Property;

/**
 * Class PropertyTrigger
 * @package App\Model
 */
class PropertyTrigger extends AbstractTrigger
{
    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected static $name = self::PROPERTY_BASED_TRIGGER;

    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected static $description = 'Property based trigger';

    /**
     * @Groups({"TRIGGER"})
     * @var Filter|[]
     */
    protected $filters = [];

    /**
     * @return Filter|[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $filters
     * @return PropertyTrigger
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }
}