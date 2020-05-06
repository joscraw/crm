<?php

namespace App\Dto;

use App\Dto\DataTransformer\AclLock_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class AclLock_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::ACL_LOCK})
 *
 */
class AclLock_Dto extends Dto
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
     * @SWG\Property(property="fieldName", type="string", example="first_name")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $fieldName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AclLock_Dto
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return AclLock_Dto
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
     * @return AclLock_Dto
     */
    public function setClassType($classType)
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param $fieldName
     * @return AclLock_Dto
     */
    public function setFieldName($fieldName): AclLock_Dto
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getDataTransformer()
    {
        return AclLock_DtoTransformer::class;
    }
}