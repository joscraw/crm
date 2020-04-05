<?php

namespace App\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class SelectizeSearchResultPropertyTransformer implements DataTransformerInterface
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
     * @param $properties
     * @return array|ArrayCollection
     */
    public function reverseTransform($properties)
    {
        // no issue number? It's optional, so that's ok
        if (empty($properties)) {
            return [];
        }

        return new ArrayCollection($properties);
    }
}