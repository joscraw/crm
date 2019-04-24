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

class RecordMultipleCheckboxTransformer implements DataTransformerInterface
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
     * @param $text
     * @return array
     * @throws \Exception
     */
    public function transform($text)
    {

        if($text === null || empty($text)) {
            return [];
        }

        return explode(";", $text);
    }

    /**
     * Transforms an id (record) to an object (issue).
     * @param $array
     * @return float
     */
    public function reverseTransform($array)
    {
        if (empty($array) || $array === null) {
            return '';
        }


        return implode(";", $array);
    }
}