<?php

namespace App\Dto;

use App\Dto\DataTransformer\AclEntry_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class AclEntry_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::ACL_ENTRY})
 *
 */
class AclEntry_Dto extends Dto
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
     * @SWG\Property(property="mask", type="integer", example=15, description="Possible values are 1,2,4,8, and the sum of any of those.")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a mask.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var integer
     */
    private $mask;

    /**
     * @SWG\Property(property="objectIdentifier", type="integer", example=3)
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget an object identifier.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var integer
     */
    private $objectIdentifier;

    /**
     * @SWG\Property(property="classType", type="string", example=DtoFactory::CUSTOM_OBJECT)
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a class type.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $classType;

    /**
     *
     * @SWG\Property(property="securityIdentity", type="string", example="App\Entity\User-23")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a security identity.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $securityIdentity;

    /**
     * @SWG\Property(property="granting", type="boolean", default=true, example=true)
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     */
    private $granting = true;

    /**
     * @SWG\Property(property="grantingStrategy", type="array", example={"create", "read", "update", "delete", "all"}, @SWG\Items(type="string"))
     *
     * @Groups({Dto::GROUP_DEFAULT})
     */
    private $grantingStrategy = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AclEntry_Dto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @param int $mask
     * @return AclEntry_Dto
     */
    public function setMask($mask)
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param int $objectIdentifier
     * @return AclEntry_Dto
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        return $this->classType;
    }

    /**
     * @param string $classType
     * @return AclEntry_Dto
     */
    public function setClassType($classType)
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecurityIdentity(): string
    {
        return $this->securityIdentity;
    }

    /**
     * @param string $securityIdentity
     * @return AclEntry_Dto
     */
    public function setSecurityIdentity(string $securityIdentity): AclEntry_Dto
    {
        $this->securityIdentity = $securityIdentity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGranting()
    {
        return $this->granting;
    }

    /**
     * @param mixed $granting
     * @return AclEntry_Dto
     */
    public function setGranting($granting): AclEntry_Dto
    {
        $this->granting = $granting;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGrantingStrategy()
    {
        return $this->grantingStrategy;
    }

    /**
     * @param mixed $grantingStrategy
     * @return AclEntry_Dto
     */
    public function setGrantingStrategy($grantingStrategy): AclEntry_Dto
    {
        $this->grantingStrategy = $grantingStrategy;

        return $this;
    }

    public function getDataTransformer()
    {
        return AclEntry_DtoTransformer::class;
    }
}