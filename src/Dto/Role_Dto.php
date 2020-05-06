<?php

namespace App\Dto;

use App\Annotation\Link;
use App\Dto\DataTransformer\Role_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;
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
     * @var integer
     *
     * @SWG\Property(property="id", type="integer", example=1)
     */
    public $id;

    /**
     *
     * @SWG\Property(property="name", type="string", example="Accountant")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     * @Assert\NotBlank(message="Don't forget a name for your role.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $name;

    /**
     *
     * @SWG\Property(property="description", type="string", example="Read only access to invoice objects.")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $description;


    /**
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @SWG\Property(property="_links", type="object",
     *      @SWG\Property(property="new", type="string", example="/api/v1/private/roles/new"),
     *      @SWG\Property(property="view", type="string", example="/api/v1/private/roles/1/view"),
     *      @SWG\Property(property="edit", type="string", example="/api/v1/private/roles/1/edit"),
     *      @SWG\Property(property="delete", type="string", example="/api/v1/private/roles/1/delete")
     *
     *  )
     */
    protected $_links = [];


    /**
     * @SWG\Property(property="internalIdentifier", type="integer", example=9874561920)
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT})
     *
     * @CustomAssert\PortalNotFoundForInternalIdentifier(groups={Dto::GROUP_CREATE})
     *
     * @var integer
     */
    private $internalIdentifier;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return Role_Dto
     */
    public function setId($id)
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
     * @return Role_Dto
     */
    public function setName(string $name): Role_Dto
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getInternalIdentifier(): ?int
    {
        return $this->internalIdentifier;
    }

    /**
     * @param int $internalIdentifier
     */
    public function setInternalIdentifier(?int $internalIdentifier): void
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return string
     */
    public function getDataTransformer()
    {
        return Role_DtoTransformer::class;
    }
}