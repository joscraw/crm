<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

abstract class Dto
{
    public const GROUP_DEFAULT = 'default';
    public const GROUP_CREATE = 'create';
    public const GROUP_UPDATE = 'update';

    /**
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @SWG\Property(property="_links", type="object",
     *      @SWG\Property(property="new", type="string", example="/api/v1/private/custom-objects/new"),
     *      @SWG\Property(property="view", type="string", example="/api/v1/private/custom-objects/1/view"),
     *      @SWG\Property(property="edit", type="string", example="/api/v1/private/custom-objects/1/edit"),
     *      @SWG\Property(property="delete", type="string", example="/api/v1/private/custom-objects/1/delete")
     *
     *  )
     */
    protected $_links = [];

    abstract public function getDataTransformer();

    public function getLinks()
    {
        return $this->_links;
    }

    public function getLink($ref)
    {
        if(isset($this->_links[$ref])) {
            return $this->_links[$ref];
        }
        return '';
    }

    /**
     * @param $links
     * @return Dto
     */
    public function setLinks($links)
    {
        $this->_links = $links;

        return $this;
    }
}