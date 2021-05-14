<?php

namespace App\Form\DataTransformer;

use App\Repository\RecordRepository;
use App\Utils\ArrayHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class RecordNumberCurrencyTransformer implements DataTransformerInterface
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

        if($number === null || empty($number) || !is_numeric($number)) {
            return;
        }

        return $number;

    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $number
     * @return float
     */
    public function reverseTransform($number)
    {
        if (empty($number)) {
            return '';
        }

        if(!is_numeric($number)) {
            return '';
        }

        $formattedNumber = number_format((float)$number, 2, '.', '');
        return $formattedNumber;

    }
}