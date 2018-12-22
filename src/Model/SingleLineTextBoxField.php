<?php

namespace App\Model;

use JsonSerializable;

/**
 * Class SingleLineTextBoxProperty
 * @package App\Model
 */
class SingleLineTextBoxField extends AbstractField implements JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = 'single_line_text_box_field';

    /**
     * @var string
     */
    protected static $description = 'Single line text box field';

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