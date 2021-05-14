<?php

namespace App\Validator\Constraints;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EmailExistsValidator
 * @package App\Validator\Constraints
 */
class EmailExistsValidator extends ConstraintValidator
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * EmailExistsValidator constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @param $emailAddress
     * @param Constraint $constraint
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($emailAddress, Constraint $constraint)
    {

        $user = $this->userRepository->getByEmailAddress($emailAddress);

        if(null === $user) {

            $this->context->buildViolation($constraint->message)
                ->atPath('email')
                ->addViolation();

        }
    }
}