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
     * @param $date
     * @return string
     */
    public function transform($date)
    {

        if($date === null || empty($date)) {
            return;
        }

        $date = \DateTime::createFromFormat(DatePickerField::$storedFormat, $date);
        $date = $date->format('m-d-Y');
        $date = \DateTime::createFromFormat('m-d-Y', $date);
        return $date;

    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $date
     * @return Record
     */
    public function reverseTransform($date)
    {
        if (empty($date) || !($date instanceof \DateTime)) {
            return;
        }

        return $date->format(DatePickerField::$storedFormat);

    }
}