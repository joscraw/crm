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

class RecordNumberUnformattedTransformer implements DataTransformerInterface
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
     * @param $number
     * @return string
     * @throws \Exception
     */
    public function transform($number)
    {

        if($number === null || empty($number)) {
            return;
        }

        return $number;

    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $number
     * @return string
     */
    public function reverseTransform($number)
    {
        if (empty($number)) {
            return '';
        }

        return $number;

    }
}