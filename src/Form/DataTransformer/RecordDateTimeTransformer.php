<?php

namespace App\Form\DataTransformer;

use App\Entity\Record;
use App\Model\DatePickerField;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RecordDateTimeTransformer implements DataTransformerInterface
{
    use ArrayHelper;

    private $entityManager;

    private $recordRepository;

    public function __construct(EntityManagerInterface $entityManager, RecordRepository $recordRepository)
    {
        $this->entityManager = $entityManager;
        $this->recordRepository = $recordRepository;
    }

    /**
     * Transforms an object (record) to a string (number).
     *
     * @param $records
     * @return string
     * @throws \Exception
     */
    public function transform($records)
    {

        if($records === null || empty($records)) {
            return;
        }

        $records = $this->getArrayValuesRecursive($records);

        return (string) $records[0];
    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $record
     * @return Record
     */
    public function reverseTransform($record)
    {
        if (empty($record) || !($record instanceof \DateTime)) {
            return;
        }

        return $record->format(DatePickerField::$storedFormat);

    }
}