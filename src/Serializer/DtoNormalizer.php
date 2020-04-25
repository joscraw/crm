<?php

namespace App\Serializer;

use App\Annotation\Link;
use App\Dto\Dto;
use App\Model\AbstractField;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
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
     * DtoNormalizer constructor.
     * @param ObjectNormalizer $normalizer
     * @param Reader $annotationReader
     * @param RouterInterface $router
     */
    public function __construct(ObjectNormalizer $normalizer, Reader $annotationReader, RouterInterface $router)
    {
        $this->normalizer = $normalizer;
        $this->annotationReader = $annotationReader;
        $this->router = $router;
        $this->expressionLanguage = new ExpressionLanguage();
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

        $links = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Link) {
                try {
                    $uri = $this->router->generate(
                        $annotation->route,
                        $this->resolveParams($annotation->params, $object)
                    );
                } catch (\Exception $exception) {
                    // All our api routes are dynamically generated
                    // using our ApiLoader Class and custom Annotations.
                    // For some reason if an @Link is defined and that
                    // route name does not exist anymore then just skip
                    // adding it to the _links array
                    continue;
                }
                $links[$annotation->name] = $uri;
            }
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        if($links) {
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

    private function resolveParams(array $params, $object)
    {
        foreach ($params as $key => $param) {
            $params[$key] = $this->expressionLanguage
                ->evaluate($param, array('object' => $object));
        }
        return $params;
    }
}