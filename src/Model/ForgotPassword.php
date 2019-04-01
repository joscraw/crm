<?php

namespace App\Model;

use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ForgotPassword
 * @package App\Model
 */
class ForgotPassword
{

    /**
     * @var string
     * @Assert\Email()
     * @CustomAssert\EmailExists()
     */
    protected $emailAddress;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     * @return ForgotPassword
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

}