<?php

namespace App\Request\ParamConverter;

use App\Entity\Folder;
use App\Entity\MarketingList;
use App\Entity\Property;
use App\Entity\Record;
use App\Entity\Report;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class FolderConverter implements ParamConverterInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * FolderConverter constructor.
     * @param EntityManagerInterface $entityManager
     * @param FolderRepository $folderRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FolderRepository $folderRepository)
    {
        $this->entityManager = $entityManager;
        $this->folderRepository = $folderRepository;
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
        $folderId = $request->attributes->get('folderId');

        $folder = $this->folderRepository->find($folderId);

        if(!$folder) {
            return false;
        }

        $request->attributes->set($configuration->getName(), $folder);

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

        if($configuration->getClass() !== Folder::class) {
            return false;
        }

        return true;
    }
}