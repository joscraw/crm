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

/**
 * Class CustomObject
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
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    public $id;

    /**
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     * @Assert\NotBlank(message="Don't forget a label for your custom object.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Please only use letters, numbers, underscores and spaces.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $label;

    /**
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

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    public function getDataTransformer()
    {
        return CustomObject_DtoTransformer::class;
    }
}