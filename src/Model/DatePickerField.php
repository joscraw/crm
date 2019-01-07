<?php

namespace App\Model;

/**
 * Class DatePickerField
 * @package App\Model
 */
class DatePickerField extends AbstractField implements \JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = FieldCatalog::DATE_PICKER;

    /**
     * @var string
     */
    protected static $description = 'Date picker field';

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'name'          => $this->getName(),
                'description'   => $this->getDescription()
            ]
        );
    }


}