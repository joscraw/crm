<?php

namespace App\Dto;

use App\Annotation\Link;
use App\Dto\DataTransformer\CustomObject_DtoTransformer;
use App\Entity\CustomObject;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class CustomObject
 * @package App\Dto
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::CUSTOM_OBJECT})
 *
 * @Link(
 *  Api::LINK_VIEW,
 *  route = "api_v1_private_custom_object_view",
 *  params = { "id": "object.getId()" }
 * )
 *
 * @Link(
 *  Api::LINK_EDIT,
 *  route = "api_v1_private_custom_object_edit",
 *  params = { "id": "object.getId()" }
 * )
 *
 */
class CustomObject_Dto extends Dto
{
    /**
     * @Groups({Dto::GROUP_DEFAULT, "two"})
     *
     * @var string
     */
    public $id;

    /**
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT, "two"})
     * @Assert\NotBlank(message="Don't forget a label for your custom object.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Please only use letters, numbers, underscores and spaces.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $label;

    /**
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT, "two"})
     *
     * internal name
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Please only use letters numbers and underscores.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $internalName;

    /**
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @SWG\Property(property="_links", type="object",
     *      @SWG\Property(property="view", type="string", example="http://crm.dev/api/v1/private/custom-objects/1/view"),
     *      @SWG\Property(property="edit", type="string", example="http://crm.dev/api/v1/private/custom-objects/1/edit")
     *
     *  )
     */
    private $_links = [];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CustomObject_Dto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return CustomObject_Dto
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getInternalName()
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     * @return CustomObject_Dto
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;

        return $this;
    }

    public function getDataTransformer()
    {
        return CustomObject_DtoTransformer::class;
    }

    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * @param $links
     * @return CustomObject_Dto
     */
    public function setLinks($links)
    {
        $this->_links = $links;

        return $this;
    }
}