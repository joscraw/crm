<?php

namespace App\Dto;

use App\Annotation\Link;
use App\Dto\DataTransformer\Portal_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class Portal_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::PORTAL})
 *
 * @Link(
 *  rel= Api::LINK_NEW,
 *  href = "'/portals/new'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_VIEW,
 *  href = "'/portals/' ~ object.getId() ~ '/view'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_EDIT,
 *  href = "'/portals/' ~ object.getId() ~ '/edit'",
 *  scopes={"private"}
 * )
 *
 * @Link(
 *  rel= Api::LINK_DELETE,
 *  href = "'/portals/' ~ object.getId() ~ '/delete'",
 *  scopes={"private"}
 * )
 *
 */
class Portal_Dto extends Dto
{
    /**
     * @SWG\Property(property="id", type="integer", example=1)
     *
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(property="name", type="string", example="Southeast")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a name.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $name;

    /**
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @var bool
     */
    private $systemDefined = false;

    /**
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @SWG\Property(property="_links", type="object",
     *      @SWG\Property(property="new", type="string", example="/api/v1/private/portals/new"),
     *      @SWG\Property(property="view", type="string", example="/api/v1/private/portals/1/view"),
     *      @SWG\Property(property="edit", type="string", example="/api/v1/private/portals/1/edit"),
     *      @SWG\Property(property="delete", type="string", example="/api/v1/private/portals/1/delete")
     *
     *  )
     */
    protected $_links = [];

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Portal_Dto
     */
    public function setId($id): Portal_Dto
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Portal_Dto
     */
    public function setName(string $name): Portal_Dto
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataTransformer()
    {
        return Portal_DtoTransformer::class;
    }

    public function getSystemDefined(): ?bool
    {
        return $this->systemDefined;
    }

    public function setSystemDefined(?bool $systemDefined): self
    {
        $this->systemDefined = $systemDefined;

        return $this;
    }
}