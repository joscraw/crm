<?php

namespace App\Model;

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
    protected static $test = 'property_test';

    /**
     * @Groups({"TRIGGER"})
     * @var string
     */
    protected static $description = 'Property based trigger';

    /**
     * @Groups({"TRIGGER"})
     * @var Filter|[]
     */
    private $filters;

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
    public static function getTest(): string
    {
        return self::$test;
    }

    /**
     * @param string $test
     */
    public static function setTest(string $test): void
    {
        self::$test = $test;
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
}