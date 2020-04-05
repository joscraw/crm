<?php

namespace App\Controller;

use App\Entity\MarketingList;
use App\Entity\Portal;
use App\Utils\ServiceHelper;
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
    use ServiceHelper;

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