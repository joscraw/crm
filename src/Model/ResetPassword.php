<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\PasswordsMustMatch;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;

/**
 * Class ResetPassword
 * @package App\Model
 * @CustomAssert\PasswordsMustMatch()
 */
class ResetPassword
{

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6")
     * @Assert\NotBlank(message="Don't forget a password for your user!")
     *
     * @var string The hashed password
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Don't forget the password repeat field!")
     * @var string password repeat
     */
    private $passwordRepeat;

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordRepeat(): ?string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(?string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

}