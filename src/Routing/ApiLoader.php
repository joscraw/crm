<?php

namespace App\Routing;

use App\Utils\NamespaceHelper;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ApiLoader extends Loader
{
    use NamespaceHelper;

    /**
     * @var bool
     */
    private $isLoaded = false;

    /**
     * @var Reader $annotationReader
     */
    private $annotationReader;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var string
     */
    private $projectDirectory;

    /**
     * @var int
     */
    private $fileRecursionDepth = 100;

    /**
     * ApiLoader constructor.
     * @param Reader $annotationReader
     * @param $projectDirectory
     */
    public function __construct(Reader $annotationReader, $projectDirectory)
    {
        $this->annotationReader = $annotationReader;
        $this->projectDirectory = $projectDirectory;
        $this->expressionLanguage = new ExpressionLanguage();
    }

    /**
     * @param $resource
     * @param null $type
     * @return RouteCollection
     * @throws \ReflectionException
     */
    public function load($resource, $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $dir = $this->projectDirectory. '/src/Controller';

        $finder = new Finder();

        $finder->depth(sprintf("< %s", $this->fileRecursionDepth))->in($dir)->files()->name('*.php');

        $routes = new RouteCollection();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $namespace = $this->byToken($file->getContents());
            $class = $namespace . '\\' . $file->getBasename('.php');
            $reflectionClass = new \ReflectionClass($class);
            $reflectionMethods = $reflectionClass->getMethods();

            foreach($reflectionMethods as $reflectionMethod) {
                /** @var \App\Annotation\ApiRoute $annotation */
                $annotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'App\Annotation\ApiRoute');
                if (!$annotation) {
                    continue;
                }

                $versions = $annotation->getVersions();
                $scopes = $annotation->getScopes();

                $scopeMap = [];
                foreach($scopes as $scope) {
                    $scopeMap[$scope] = $versions;
                }

                foreach($scopeMap as $scope => $versions) {
                    foreach($versions as $version) {

                        $path = sprintf('/api/%s/%s/%s', $version, $scope, ltrim($annotation->getPath(), '/'));

                        $defaults = [
                            '_controller' => sprintf("%s::%s", $class, $reflectionMethod->getName())
                        ];
                        $route = new Route(
                            $path,
                            $defaults,
                            $annotation->getRequirements(),
                            $annotation->getOptions(),
                            $annotation->getHost(),
                            $annotation->getSchemes(),
                            $annotation->getMethods(),
                            $annotation->getCondition()
                        );
                        $routeName = sprintf("api_%s_%s_%s", $version, $scope, $annotation->getName());
                        $routes->add($routeName, $route);
                    }
                }
            }
        }

        $this->isLoaded = true;

        return $routes;
    }

    /**
     * @param $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'api' === $type;
    }
}