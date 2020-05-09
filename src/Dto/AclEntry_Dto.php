<?php

namespace App\Dto;

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
     * @SWG\Property(property="mask", type="integer", example=15, description="Possible values are 1,2,4,8,16,-1 or the sum of (1,2,4 and 8).")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a mask.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var integer
     */
    private $mask;

    /**
     * @SWG\Property(property="objectIdentifier", type="string", example="App\Entity\PropertyGroup-1")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $objectIdentifier;

    // todo we need validation here to make sure at least an object Identifier or an attribute identifier has been added.
    // todo one or the other.

    /**
     * @SWG\Property(property="attributeIdentifier", type="string", example="can:login")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $attributeIdentifier;

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
     * @return string
     */
    public function getObjectIdentifier()
    {
        return $this->objectIdentifier;
    }

    /**
     * @param string $objectIdentifier
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
    public function getAttributeIdentifier()
    {
        return $this->attributeIdentifier;
    }

    /**
     * @param string $attributeIdentifier
     * @return AclEntry_Dto
     */
    public function setAttributeIdentifier($attributeIdentifier)
    {
        $this->attributeIdentifier = $attributeIdentifier;

        return $this;
    }

    public function getDataTransformer()
    {
        return null;
    }
}