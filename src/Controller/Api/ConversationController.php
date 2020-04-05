<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConversationController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/api/conversations")
 *
 */
class ConversationController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/conversation-setup", name="conversation_setup", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setupAction(Portal $portal) {

        return $this->render('conversation/index.html.twig', array(
            'portal' => $portal
        ));
    }
}