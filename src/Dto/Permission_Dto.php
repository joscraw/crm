<?php

namespace App\Dto;

use App\Dto\DataTransformer\Permission_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class Permission_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::PERMISSION})
 *
 *
 */
class Permission_Dto extends Dto
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
     * @SWG\Property(property="scope", type="string", example="create:custom_objects")
     *
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $scope;

    /**
     *
     * @SWG\Property(property="description", type="string", example="create custom objects")
     *
     * @Groups({Dto::GROUP_DEFAULT})
     *
     * @var string
     */
    private $description;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return Permission_Dto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @return Permission_Dto
     */
    public function setScope(string $scope): Permission_Dto
    {
        $this->scope = $scope;

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
     * @return Permission_Dto
     */
    public function setDescription(?string $description): Permission_Dto
    {
        $this->description = $description;

        return $this;
    }
    /**
     * @return string
     */
    public function getDataTransformer()
    {
        return Permission_DtoTransformer::class;
    }
}