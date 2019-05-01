<?php

namespace App\Form\DataTransformer;

use App\Entity\Record;
use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IdArrayToRecordArrayTransformer implements DataTransformerInterface
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
     * @param $records[]
     * @return array
     */
    public function transform($records)
    {

        if($records === null || empty($records)) {
            return [];
        }

        return explode(";", $records);

    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $records
     * @return array|ArrayCollection
     */
    public function reverseTransform($records)
    {
        // no issue number? It's optional, so that's ok
        if (empty($records) || $records === null) {
            return '';
        }

        return implode(";", $records);
    }
}