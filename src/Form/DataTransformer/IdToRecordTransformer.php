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
     * @return string
     */
    public function transform($record)
    {

        if($record === null) {
            return '';
        }

        return $record;
    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $record
     * @return Record
     */
    public function reverseTransform($record)
    {
        if (empty($record)) {
            return '';
        }

        return $record;
    }
}