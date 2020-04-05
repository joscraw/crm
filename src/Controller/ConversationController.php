<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\User;
use App\Service\GmailProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConversationController
 * @package App\Controller
 *
 * @Route("/{internalIdentifier}/conversations")
 *
 */
class ConversationController extends AbstractController
{
    /**
     * @var GmailProvider
     */
    private $gmailProvider;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ConversationController constructor.
     * @param GmailProvider $gmailProvider
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(GmailProvider $gmailProvider, EntityManagerInterface $entityManager)
    {
        $this->gmailProvider = $gmailProvider;
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/{routing}", name="conversation_index", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal, CustomObject $customObject, SessionInterface $session) {

        /** @var User $user */
        $user = $this->getUser();
        // if the user does not have a google token then redirect to the google oauth view
        if(!$portal->getGmailAccount() || !$portal->getGmailAccount()->getGoogleToken()) {
           return $this->redirectToRoute('oauth_google_authorization');
        }
        return $this->render('conversation/index.html.twig', array(
            'portal' => $portal
        ));
    }
}