<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "single_line_text_field_condition"="App\Model\SingleLineTextFieldCondition"
 * })
 */
abstract class AbstractCondition
{
    /**
     * @var string
     */
    protected static $name = 'abstract_condition';
}