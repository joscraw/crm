<?php

namespace App\Dto\DataTransformer;

use App\Exception\DataTransformerNotFoundException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Psr\Container\ContainerInterface;

/**
 * Sometimes, a service needs access to several other services without being
 * sure that all of them will actually be used. In those cases, you may want
 * the instantiation of the services to be lazy. In our case here, we don't know which
 * data transformer we are going to need until runtime. And rather then creating a large
 * switch case factory class and injecting every service through dependency injection
 * (which is going to instantiate every single one of those), we choose to do it on the fly!
 *
 * @see https://symfony.com/doc/4.4/service_container/service_subscribers_locators.html
 *
 * Class DataTransformerFactory
 * @package App\Dto\DataTransformer
 */
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
            CustomObject_DtoTransformer::class,
            Role_DtoTransformer::class,
            User_DtoTransformer::class,
            Portal_DtoTransformer::class,
            Permission_DtoTransformer:: class
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