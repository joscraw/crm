<?php

namespace App\Request\ParamConverter;

use App\Entity\Form;
use App\Entity\MarketingList;
use App\Entity\Property;
use App\Entity\Record;
use App\Entity\Report;
use App\Repository\CustomObjectRepository;
use App\Repository\FormRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class FormConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FormRepository
     */
    private $formRepository;

    /**
     * FormConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param FormRepository $formRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FormRepository $formRepository)
    {
        $this->entityManager = $entityManager;
        $this->formRepository = $formRepository;
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
        $uid = $request->attributes->get('uid');

        $form = $this->formRepository->findOneBy([
            'uid' => $uid
        ]);

        if(!$form) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $form);

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

        if($configuration->getClass() !== Form::class) {
            return false;
        }

        return true;
    }
}