<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RecordController
 * @package App\Controller
 *
 * @Route("/{internalIdentifier}/records")
 *
 */
class RecordController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/{internalName}/{routing}", name="record_list", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Portal $portal, CustomObject $customObject) {
        return $this->render('record/list.html.twig', array(
            'portal' => $portal,
            'customObject' => $customObject,
        ));
    }
}