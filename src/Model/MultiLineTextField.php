<?php

namespace App\Model;

/**
 * Class MultiLineTextField
 * @package App\Model
 */
class MultiLineTextField extends AbstractField implements \JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = FieldCatalog::MULTI_LINE_TEXT;

    /**
     * @var string
     */
    protected static $description = 'Multi line text field';

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