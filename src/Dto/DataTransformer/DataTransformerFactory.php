<?php

namespace App\Dto\DataTransformer;

use App\Exception\DataTransformerNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Psr\Container\ContainerInterface;

class DataTransformerFactory implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $locator;

    /**
     * DataTransformerFactory constructor.
     * @param ContainerInterface $locator
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return [
            CustomObject_DtoTransformer::class
        ];
    }

    /**
     * @param $dataTransformerClass
     * @return mixed
     * @throws DataTransformerNotFoundException
     */
    public function get($dataTransformerClass)
    {
        if ($this->locator->has($dataTransformerClass)) {
            /** @var DataTransformerInterface $dataTransformer */
            return $this->locator->get($dataTransformerClass);
        }

        throw new DataTransformerNotFoundException(
            sprintf("Data transformer not found for class %s. If you know this class
            exists, make sure to whitelist it in the getSubscribedServices() method at the top of 
            this class.", $dataTransformerClass)
        );
    }

}