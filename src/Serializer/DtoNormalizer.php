<?php

namespace App\Serializer;

use App\Annotation\ApiVersion;
use App\Annotation\Link;
use App\Dto\Dto;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\NormalizationAwareInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class DtoNormalizer
 * @package App\Serializer\
 */
class DtoNormalizer implements NormalizerInterface, NormalizationAwareInterface
{

    /**
     * @var ObjectNormalizer
     */
    private $normalizer;

    /**
     * @var Reader $annotationReader
     */
    private $annotationReader;

    /**
     * @var RouterInterface
     */
    private $router;

    private $expressionLanguage;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * DtoNormalizer constructor.
     * @param ObjectNormalizer $normalizer
     * @param Reader $annotationReader
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     */
    public function __construct(ObjectNormalizer $normalizer, Reader $annotationReader, RouterInterface $router, RequestStack $requestStack)
    {
        $this->normalizer = $normalizer;
        $this->annotationReader = $annotationReader;
        $this->router = $router;
        $this->expressionLanguage = new ExpressionLanguage();
        $this->requestStack = $requestStack;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param mixed $object Object to normalize
     * @param string $format Format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return array|string|int|float|bool
     *
     * @throws InvalidArgumentException   Occurs when the object given is not an attempted type for the normalizer
     * @throws CircularReferenceException Occurs when the normalizer detects a circular reference when no circular
     *                                    reference handler can fix it
     * @throws LogicException             Occurs when the normalizer is not called in an expected context
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \ReflectionException
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $reflectionClass = new \ReflectionClass($object);

        $annotations = $this->annotationReader->getClassAnnotations($reflectionClass);

        $request = $this->requestStack->getCurrentRequest();
        $version = $request->headers->get('X-Accept-Version');
        $scope = $request->headers->get('X-Accept-Scope');

        $data = $this->normalizer->normalize($object, $format, $context);

        // If we can't detect the version or scope from the
        // request, then do not attempt to add _links to the data
        if(!$version || !$scope) {
            return $data;
        }

        $links = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                try {

                    $href = $this->resolveHref($annotation->href, $object);

                } catch (\Exception $exception) {
                    // All our api routes are dynamically generated
                    // using our ApiLoader Class and custom Annotations.
                    // For some reason if an @Link is defined and that
                    // route name does not exist anymore then just skip
                    // adding it to the _links array
                    continue;
                }
                $links[$annotation->rel] = sprintf("/api/%s/%s%s",
                    !empty($version) ? $version : '',
                    !empty($scope) ? $scope : '',
                    $href
                    );
            }
        }

        if(!empty($links)) {
            $data['_links'] = $links;
        }

        return $data;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed $data Data to normalize
     * @param string $format The format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        if(is_subclass_of($data, Dto::class)) {
            return true;
        }

        return false;
    }

    private function resolveHref($href, $object)
    {
        return $this->expressionLanguage
            ->evaluate($href, array('object' => $object));
    }
}