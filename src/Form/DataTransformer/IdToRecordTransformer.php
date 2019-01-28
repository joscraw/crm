<?php

namespace App\Form\DataTransformer;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IdToRecordTransformer implements DataTransformerInterface
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
     * @return array
     */
    public function transform($records)
    {

        if($records === null) {
            return '';
        }

        if(!$records) {
            return [];
        }

        $records = $this->getArrayValuesRecursive($records);


        return $records;
    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $record
     * @return Record
     */
    public function reverseTransform($record)
    {
        if (empty($record)) {
            return;
        }

        $record = $this->recordRepository->find($record);

        return $record;
    }
}