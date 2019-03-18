<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use App\Repository\RoleRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class RoleNameAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class RoleNameAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * RoleNameAlreadyExistsValidator constructor.
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {
        $name = $protocol->getName();
        $portal = $protocol->getPortal();
        $roles = $this->roleRepository->getRolesByNameAndPortal($portal, $name);

        foreach ($roles as $role) {
            if ($role->getId() !== $protocol->getId()) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ string }}', $name)
                    ->atPath('name')
                    ->addViolation();
            }
        }
    }
}