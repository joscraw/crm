<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

abstract class Dto
{
    public const GROUP_DEFAULT = 'default';
    public const GROUP_CREATE = 'create';
    public const GROUP_UPDATE = 'update';
    public const GROUP_DELETE = 'delete';

    abstract public function getDataTransformer();

    protected $_links = [];

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * @param mixed $links
     */
    public function setLinks($links): void
    {
        $this->_links = $links;
    }

}