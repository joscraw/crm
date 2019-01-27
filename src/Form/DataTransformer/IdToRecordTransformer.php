<?php

namespace App\Form\DataTransformer;

use App\Entity\Record;
use App\Repository\RecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IdToRecordTransformer implements DataTransformerInterface
{
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
     * @param $properties[]
     * @return array
     */
    public function transform($properties)
    {

        if($properties === null) {
            return '';
        }

        /*if(!$properties) {
            return;
        }*/

        if(!$properties) {
            return [];
        }

        $propertiesArray =[];
        foreach($properties as $property) {
            $propertiesArray[] = $property;
        }

        return $propertiesArray;
    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $record
     * @return Record
     */
    public function reverseTransform($record)
    {
        // no issue number? It's optional, so that's ok
        if (empty($record)) {
            return;
        }

        $record = $this->recordRepository->find($record);

        return $record;
    }
}