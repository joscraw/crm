<?php

namespace App\Form\DataTransformer;

use App\Entity\Record;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (record) to a string (number).
     *
     * @param $properties[]
     * @return array
     */
    public function transform($properties)
    {
        $propertiesArray =[];
        foreach($properties as $property) {
            $propertiesArray[] = $property;
        }

        return $propertiesArray;
    }

    /**
     * Transforms an id (record) to an object (issue).
     *
     * @param $recordId
     * @return Record|null
     */
    public function reverseTransform($recordId)
    {
        // no issue number? It's optional, so that's ok
        if (!$recordId) {
            return;
        }

        $record = $this->entityManager
            ->getRepository(Record::class)
            // query for the issue with this id
            ->find($recordId);

        if (null === $record) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(
                sprintf(
                    'A record with id "%s" does not exist!',
                    $recordId
                )
            );
        }

        return $record;
    }
}