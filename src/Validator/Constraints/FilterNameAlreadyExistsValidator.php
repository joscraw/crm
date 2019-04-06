<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\PropertyRepository;
use App\Repository\RoleRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class FilterNameAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class FilterNameAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var FilterRepository
     */
    private $filterRepository;

    /**
     * FilterNameAlreadyExistsValidator constructor.
     * @param FilterRepository $filterRepository
     */
    public function __construct(FilterRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {
        $name = $protocol->getName();
        $portal = $protocol->getPortal();
        $type = $protocol->getType();

        $filter = $this->filterRepository->findOneBy([
            'portal' => $portal->getId(),
            'name' => $name,
            'type' => $type
        ]);

        if($filter) {

            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $name)
                ->atPath('name')
                ->addViolation();

        }
    }
}