<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class NumberField
 * @package App\Model
 */
class NumberField extends AbstractField
{

    const UNFORMATTED_NUMBER = 'UNFORMATTED_NUMBER';

    const CURRENCY = 'CURRENCY';

    /**
     * Used in the form
     * @var array
     */
    public static $types = [
        'Unformatted Number' => self::UNFORMATTED_NUMBER,
        'Currency' => self::CURRENCY,
    ];

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $name = FieldCatalog::NUMBER;

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected static $description = 'Number field';

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     * @var string
     */
    protected $type;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function isCurrency() {
        return $this->type === NumberField::$types['Currency'];
    }

    public function isUnformattedNumber() {
        return $this->type === NumberField::$types['Unformatted Number'];
    }
}