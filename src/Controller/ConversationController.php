<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\User;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\GmailProvider;
use App\Service\MessageGenerator;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Gmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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