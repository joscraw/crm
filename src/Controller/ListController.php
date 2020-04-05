<?php

namespace App\Controller;

use App\Entity\MarketingList;
use App\Entity\Portal;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ListController
 * @package App\Controller
 *
 * @Route("/{internalIdentifier}/lists")
 *
 */
class ListController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * ListController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param FolderRepository $folderRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        FolderRepository $folderRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->folderRepository = $folderRepository;
    }


    /**
     * @Route("/create/{routing}", name="create_list", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Portal $portal) {

        return $this->render('list/create.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{listId}/edit/{routing}", name="edit_list", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param MarketingList $list
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(MarketingList $list, Portal $portal) {

        return $this->render('list/edit.html.twig', array(
            'portal' => $portal
        ));
    }

    /**
     * @Route("/{routing}", name="list_settings", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listSettingsAction(Portal $portal) {

        return $this->render('list/settings.html.twig', array(
            'portal' => $portal
        ));
    }

}