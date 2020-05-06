<?php

namespace App\Dto;

use App\Annotation\Link;
use App\Dto\DataTransformer\CustomObject_DtoTransformer;
use App\Entity\Portal;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use App\Validator\Constraints as CustomAssert;
use Swagger\Annotations as SWG;

/**
 * Class CustomObject_Dto
 * @package App\Dto
 *
 * @CustomAssert\CustomObjectLabelAlreadyExists(groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::CUSTOM_OBJECT})
 *
 *
 * @Link(
 *  rel= Api::LINK_NEW,
 *  href = "'/custom-objects/new'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_VIEW,
 *  href = "'/custom-objects/' ~ object.getId() ~ '/view'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_EDIT,
 *  href = "'/custom-objects/' ~ object.getId() ~ '/edit'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_DELETE,
 *  href = "'/custom-objects/' ~ object.getId() ~ '/delete'",
 *  scopes={"private"}
 * )
 *
 */
class CustomObject_Dto extends Dto
{
    /**
     * @SWG\Property(property="id", type="integer", example=1)
     *
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    public $id;

    /**
     *
     * @SWG\Property(property="label", type="string", example="My Custom Object")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     * @Assert\NotBlank(message="Don't forget a label for your custom object.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Please only use letters, numbers, underscores and spaces.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $label;

    /**
     * @SWG\Property(property="internalName", type="string", example="my_custom_object")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT})
     *
     * internal name
     *
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Please only use letters numbers and underscores.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $internalName;

    private $portal;

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

    /**
     * @return Portal|null
     */
    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    /**
     * @param Portal $portal
     * @return CustomObject_Dto
     */
    public function setPortal(Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataTransformer()
    {
        return CustomObject_DtoTransformer::class;
    }
}