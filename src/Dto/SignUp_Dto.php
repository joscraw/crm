<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use App\Validator\Constraints as CustomAssert;
use Swagger\Annotations as SWG;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

/**
 * Class Signup_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::SIGN_UP})
 *
 */
class SignUp_Dto extends Dto
{

    /**
     * @SWG\Property(property="email", type="string", example="joshuacrawmer@gmail.com")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget an email.", groups={Dto::GROUP_CREATE})
     * @CustomAssert\EmailAlreadyExists(groups={Dto::GROUP_CREATE})
     *
     * @var string
     */
    private $email;

    /**
     * @SWG\Property(property="password", type="string", example="A54dWinwjBOm7M&k20rJ")
     *
     * @Groups({Dto::GROUP_CREATE})
     *
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={Dto::GROUP_CREATE})
     * @Assert\NotBlank(message="Don't forget a password.", groups={Dto::GROUP_CREATE})
     *
     * @var string
     */
    private $password;

    /**
     * @SWG\Property(property="firstName", type="string", example="Josh")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a first name.", groups={Dto::GROUP_CREATE})
     *
     * @var string
     */
    private $firstName;

    /**
     * @SWG\Property(property="lastName", type="string", example="Crawmer")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a last name.", groups={Dto::GROUP_CREATE})
     *
     * @var string
     */
    private $lastName;

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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return SignUp_Dto
     */
    public function setEmail($email): SignUp_Dto
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
     * @return SignUp_Dto
     */
    public function setPassword($password): SignUp_Dto
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
     * @return SignUp_Dto
     */
    public function setFirstName($firstName): SignUp_Dto
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
     * @return SignUp_Dto
     */
    public function setLastName($lastName): SignUp_Dto
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName() {
        return $this->firstName . ' ' . $this->lastName;
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

    public function getDataTransformer()
    {
        return null;
    }
}