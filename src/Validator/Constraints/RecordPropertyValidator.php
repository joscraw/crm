<?php

namespace App\Validator\Constraints;

use App\Entity\Property;
use App\Entity\Record;
use App\Model\Filter\AndCriteria;
use App\Model\Filter\Column;
use App\Model\Filter\Filter;
use App\Model\Filter\FilterData;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyPropertyValidator
 * @package App\Validator\Constraints
 */
class RecordPropertyValidator extends ConstraintValidator
{
    use RandomStringGenerator;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RecordPropertyValidator constructor.
     * @param RecordRepository $recordRepository
     * @param PropertyRepository $propertyRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        RecordRepository $recordRepository,
        PropertyRepository $propertyRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->recordRepository = $recordRepository;
        $this->propertyRepository = $propertyRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($protocol, Constraint $constraint)
    {
        if(!$protocol instanceof Record) {
            return;
        }

        $this->validateEmail($protocol, $constraint);

    }

    /**
     * @param Record $record
     * @param Constraint $constraint
     * @return $this
     */
    private function validateEmail(Record $record, Constraint $constraint) {

        if($record->getCustomObject()->getInternalName() !== 'contacts') {
            return $this;
        }

        $email = $record->email;

        if(empty($email)) {
            return $this;
        }

        $emailProperty = $this->entityManager->getRepository(Property::class)->findOneBy([
            'customObject' => $record->getCustomObject(),
            'internalName' => 'email'
        ]);

        if(!$emailProperty) {
            return $this;
        }

        $uid = $this->generateRandomNumber(5);
        $filter = new Filter();
        $filter->setProperty($emailProperty);
        $filter->setValue($email);
        $filter->setOperator(Filter::EQ);
        $filter->setUid($uid);
        $andCriteria = new AndCriteria();
        $andCriteria->setUid($uid);
        $filterData = new FilterData();
        $filterData->getFilterCriteria()->addAndCriteria($andCriteria);
        $filterData->setBaseObject($record->getCustomObject());
        $filterData->addFilter($filter);
        $results = $filterData->runQuery($this->entityManager);

        // If we are creating a new contact record and the email already exists
        if(!$record->getId() && $results['count'] > 0) {
            $this->context->buildViolation($constraint->emailAlreadyExistsMessage )
                ->setParameter('{{ string }}', $email)
                ->atPath('email')
                ->addViolation();
            return $this;
        }

        // If we are editing a contact record don't throw an error if the email belongs to them
        if($record->getId() && $results['count'] > 0 && !empty($results['results'])) {
            // There should never be more than one result set in the query
            // response as we aren't allowing duplicates across the platform.
            // But just in case there are let's aggregate the response into an array of IDs
            $contactRecordIds = array_map(function($result){ return !empty($result['id']) ? $result['id'] : null; }, $results['results']);
            if(!in_array($record->getId(), $contactRecordIds)) {
                $this->context->buildViolation($constraint->emailAlreadyExistsMessage )
                    ->setParameter('{{ string }}', $email)
                    ->atPath('email')
                    ->addViolation();
                return $this;
            }
        }

        return $this;
    }
}