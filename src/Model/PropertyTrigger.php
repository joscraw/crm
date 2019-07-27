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
    protected static $name = 'property_trigger';

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
     * @Groups({"TRIGGER"})
     * @var CustomObject
     */
    protected $customObject;

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

    /**
     * @return string
     */
    public static function getName(): string
    {
        return self::$name;
    }

    /**
     * @param string $name
     */
    public static function setName(string $name): void
    {
        self::$name = $name;
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return self::$description;
    }

    /**
     * @param string $description
     */
    public static function setDescription(string $description): void
    {
        self::$description = $description;
    }

    /**
     * @return CustomObject
     */
    public function getCustomObject()
    {
        return $this->customObject;
    }

    /**
     * @param CustomObject $customObject
     * @return $this
     */
    public function setCustomObject(CustomObject $customObject = null)
    {
        $this->customObject = $customObject;

        return $this;
    }
}