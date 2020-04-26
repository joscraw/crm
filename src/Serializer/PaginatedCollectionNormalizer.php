<?php

namespace App\Serializer;

use App\Model\Pagination\PaginationCollection;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;

/**
 * When you call $this->normalizer->normalize() from inside a custom normalizer like this
 * and you want another one of your custom normalizer's to fire from inside this, then
 * you need to implement NormalizerAwareInterface like this class does so this normalizer
 * is aware of your custom normalizers. If you don't need a custom normalizer to call nested
 * custom normalizers you can just implement NormalizationAwareInterface
 *
 * @see https://stackoverflow.com/questions/43569313/symfony3-serializing-nested-entities
 *
 * Class DtoNormalizer
 * @package App\Serializer\
 */
class PaginatedCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{

    use NormalizerAwareTrait;


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
     * @param Reader $annotationReader
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     */
    public function __construct(Reader $annotationReader, RouterInterface $router, RequestStack $requestStack)
    {
        $this->annotationReader = $annotationReader;
        $this->router = $router;
        $this->requestStack = $requestStack;
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
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var PaginationCollection $object */

        $route = $this->requestStack->getCurrentRequest()->get('_route');
        $routeParams = $this->requestStack->getCurrentRequest()->get('_route_params');

        $createLinkUrl = function($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ));
        };

        $items = [];
        foreach($object->getItems() as $item) {
            $items[] = $this->normalizer->normalize($item, $format, $context);
        }

        $data = [];

        $data['items'] = $items;

        $pagerfanta = $object->getPagerfanta();

        $links = [
            'self' => $createLinkUrl($pagerfanta->getCurrentPage()),
            'first' => $createLinkUrl(1),
            'last' => $createLinkUrl($object->getPagerfanta()->getNbPages()),
            'next' => $pagerfanta->hasNextPage() ? $createLinkUrl($pagerfanta->getNextPage()) : null,
            'prev' => $pagerfanta->hasPreviousPage() ? $createLinkUrl($pagerfanta->getPreviousPage()) : null
        ];

        $data['_links'] = $links;

        $data = ['total' => $object->getPagerfanta()->getNbResults(), 'count' => count($object->getItems())] + $data;

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
        if($data instanceof PaginationCollection) {
            return true;
        }

        return false;
    }
}