<?php

namespace App\Validator\Constraints;

use App\Repository\PortalRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PortalNotFoundForInternalIdentifierValidator
 * @package App\Validator\Constraints
 */
class PortalNotFoundForInternalIdentifierValidator extends ConstraintValidator
{
    /**
     * @var PortalRepository
     */
    private $portalRepository;

    /**
     * PortalNotFoundForInternalIdentifierValidator constructor.
     * @param PortalRepository $portalRepository
     */
    public function __construct(PortalRepository $portalRepository)
    {
        $this->portalRepository = $portalRepository;
    }

    /**
     * @param $internalIdentifier
     * @param Constraint $constraint
     */
    public function validate($internalIdentifier, Constraint $constraint)
    {
        if(empty($internalIdentifier)) {
            return;
        }

        $portal = $this->portalRepository->findOneBy([
            'internalIdentifier' => $internalIdentifier
        ]);

        if($portal) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}