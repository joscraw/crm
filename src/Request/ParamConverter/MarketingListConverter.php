<?php

namespace App\Request\ParamConverter;

use App\Entity\MarketingList;
use App\Repository\MarketingListRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class MarketingListConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MarketingListRepository
     */
    private $marketingListRepository;

    /**
     * MarketingListConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param MarketingListRepository $marketingListRepository
     */
    public function __construct(EntityManagerInterface $entityManager, MarketingListRepository $marketingListRepository)
    {
        $this->entityManager = $entityManager;
        $this->marketingListRepository = $marketingListRepository;
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
        $listId = $request->attributes->get('listId');

        $list = $this->marketingListRepository->find($listId);

        if(!$list) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $list);

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

        if($configuration->getClass() !== MarketingList::class) {
            return false;
        }

        return true;
    }
}