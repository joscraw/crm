<?php

namespace App\Dto;

use App\Dto\DataTransformer\CustomObject_DtoTransformer;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

/**
 * Class User_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::USER})
 *
 */
class User_Dto extends Dto
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
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget an email.", groups={"CREATE", "EDIT"})
     *
     * @var string
     */
    private $email;

    /**
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     * @Assert\NotBlank(message="Don't forget a password.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $password;

    /**
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a first name.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $firstName;

    /**
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a last name.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $lastName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return User_Dto
     */
    public function setId($id): User_Dto
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User_Dto
     */
    public function setEmail($email): User_Dto
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User_Dto
     */
    public function setPassword($password): User_Dto
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return User_Dto
     */
    public function setFirstName($firstName): User_Dto
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return User_Dto
     */
    public function setLastName($lastName): User_Dto
    {
        $this->lastName = $lastName;

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