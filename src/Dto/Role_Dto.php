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
 * Class Role_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::ROLE})
 *
 *
 * @Link(
 *  rel= Api::LINK_NEW,
 *  href = "'/roles/new'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_VIEW,
 *  href = "'/roles/' ~ object.getId() ~ '/view'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_EDIT,
 *  href = "'/roles/' ~ object.getId() ~ '/edit'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_DELETE,
 *  href = "'/roles/' ~ object.getId() ~ '/delete'",
 *  scopes={"private"}
 * )
 *
 */
class Role_Dto extends Dto
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
     * @Assert\NotBlank(message="Don't forget a name for your role.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     * @Assert\Regex("/^[a-zA-Z0-9_\s]*$/", message="Please only use letters, numbers, underscores and spaces.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $name;


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