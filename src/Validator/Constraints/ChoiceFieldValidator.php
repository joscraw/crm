<?php

namespace App\Validator\Constraints;

use App\Model\FieldCatalog;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Utils\ArrayHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class ChoiceFieldValidator
 * @package App\Validator\Constraints
 */
class ChoiceFieldValidator extends ConstraintValidator
{
    use ArrayHelper;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @PropertyAlreadyExists constructor.
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(PropertyGroupRepository $propertyGroupRepository) {
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($protocol, Constraint $constraint)
    {

        if(!in_array($protocol->getFieldType(), [
            FieldCatalog::DROPDOWN_SELECT,
            FieldCatalog::MULTIPLE_CHECKBOX,
            FieldCatalog::RADIO_SELECT])) {
            return;
        }

        if(empty($protocol->getField()->getOptions())) {
            return;
        }

        $options = $protocol->getField()->getOptions();

        $dupeArray = array();
        foreach ($options as $key => $option) {
            $dupeArray[] = $option->getLabel();
        }

        $duplicateValues = $this->getKeysForDuplicateValues($dupeArray, false, true);

        foreach($duplicateValues as $value => $duplicateKeyArray) {

            if($value === '') {
                continue;
            }

            foreach($duplicateKeyArray as $duplicateKey) {
                $this->context->buildViolation($constraint->duplicateOptionMessage)
                    ->atPath(sprintf('field.options[%s].label', $duplicateKey))
                    ->addViolation();
            }
        }
    }
}