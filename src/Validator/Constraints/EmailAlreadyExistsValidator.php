<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use App\Security\Auth0MgmtApi;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EmailAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class EmailAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Auth0MgmtApi
     */
    protected $auth0MgmtApi;

    /**
     * EmailExistsValidator constructor.
     * @param UserRepository $userRepository
     * @param Auth0MgmtApi $auth0MgmtApi
     */
    public function __construct(UserRepository $userRepository, Auth0MgmtApi $auth0MgmtApi)
    {
        $this->userRepository = $userRepository;
        $this->auth0MgmtApi = $auth0MgmtApi;
    }

    /**
     * @param $emailAddress
     * @param Constraint $constraint
     */
    public function validate($emailAddress, Constraint $constraint)
    {
        if(empty($emailAddress)) {
            return;
        }

        $response = $this->auth0MgmtApi->searchUsersByEmail($emailAddress);

        if(empty($response)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();

        // todo I hate how we have to be checking both our site and auth0.
        // todo honestly the source of truth needs to be auth0 as this is
        // todo how you actually authenticate.

        /*$user = $this->userRepository->getByEmailAddress($emailAddress);

        if(null === $user) {
            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();
        }*/
    }
}