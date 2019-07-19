<?php

namespace App\Model;

use JsonSerializable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @DiscriminatorMap(typeProperty="name", mapping={
 *    "property_trigger"="App\Model\PropertyTrigger"
 * })
 */
abstract class AbstractTrigger
{
}