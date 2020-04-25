<?php

namespace App\Http\Request\ParamConverter;

use App\Entity\Filter;
use App\Repository\FilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class FilterConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FilterRepository
     */
    private $filterRepository;

    /**
     * FilterConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param FilterRepository $filterRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FilterRepository $filterRepository)
    {
        $this->entityManager = $entityManager;
        $this->filterRepository = $filterRepository;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $filterId = $request->attributes->get('filterId');

        $filter = $this->filterRepository->find($filterId);

        if(!$filter) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $filter);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {

        if($configuration->getClass() !== Filter::class) {
            return false;
        }

        return true;
    }
}