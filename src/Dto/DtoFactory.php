<?php

namespace App\Dto;

use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use App\Exception\DtoNotFoundException;
use App\Utils\NamespaceHelper;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DtoFactory
{
    use NamespaceHelper;

    /**#@+
     * A list of identifiers used by the @Identifier annotation
     * @var string
     */
    const CUSTOM_OBJECT = 'custom_object';
    /**#@-*/

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Reader $annotationReader
     */
    private $annotationReader;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var int
     */
    private $fileRecursionDepth = 100;

    /**
     * Constructor
     *
     * @param Container $container
     * @param Reader $annotationReader
     * @param $projectDirectory
     */
    public function __construct(ContainerInterface $container, Reader $annotationReader, $projectDirectory)
    {
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->projectDirectory = $projectDirectory;
    }

    /**
     * @param $identifier
     * @param $version
     * @param bool $instantiate
     * @return object|string
     * @throws DtoNotFoundException
     * @throws \ReflectionException
     */
    public function create($identifier, $version, $instantiate = false) {
        switch ($identifier) {
            case self::CUSTOM_OBJECT:
                $dto = $this->fetchDtoFromIdentifierAndVersion($identifier, $version, $instantiate);
                break;
            default:
                throw new DtoNotFoundException(
                    sprintf(
                        "Dto not found for identifier: %s and version: %s",
                        $identifier,
                        $version
                    ));
                break;
        }

        return $dto;
    }

    /**
     * @param $identifier
     * @param $version
     * @param $instantiate
     * @return object|string
     * @throws DtoNotFoundException
     * @throws \ReflectionException
     */
    private function fetchDtoFromIdentifierAndVersion($identifier, $version, $instantiate = false) {

        $dir = $this->projectDirectory. '/src/Dto';

        $finder = new Finder();

        $finder->depth(sprintf("< %s", $this->fileRecursionDepth))->in($dir)->files()->name('*.php');

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $namespace = $this->byToken($file->getContents());
            $class = $namespace . '\\' . $file->getBasename('.php');
            $reflectionClass = new \ReflectionClass($class);

            if(!$reflectionClass->isSubclassOf(Dto::class)) {
                continue;
            }

            /** @var ApiVersion $apiVersionAnnotation */
            $apiVersionAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, 'App\Annotation\ApiVersion');
            /** @var Identifier $identifierAnnotation */
            $identifierAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, 'App\Annotation\Identifier');

            if (!$apiVersionAnnotation && !$identifierAnnotation) {
                continue;
            }

            // There is a chance that more than one dto class can exist with the same
            // identifier and version. We don't care. Just return the first one.
            if(in_array($identifier, $identifierAnnotation->getIdentifiers()) &&
                in_array($version, $apiVersionAnnotation->getVersions())) {

                if($instantiate) {
                    return $reflectionClass->newInstance();
                } else {
                    return $reflectionClass->getName();
                }
            }
        }

        throw new DtoNotFoundException(
            sprintf(
                "Dto not found for identifier: %s and version: %s",
                $identifier,
                $version
            ));
    }
}