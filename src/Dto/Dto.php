<?php

namespace App\Dto;

abstract class Dto
{
    public const GROUP_DEFAULT = 'default';
    public const GROUP_CREATE = 'create';
    public const GROUP_UPDATE = 'update';

    abstract public function getDataTransformer();
}