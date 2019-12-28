<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\GoogleOauth;
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
     * @var GoogleOauth
     */
    private $googleOauth;

    /**
     * OauthController constructor.
     * @param GoogleOauth $googleOauth
     */
    public function __construct(GoogleOauth $googleOauth)
    {
        $this->googleOauth = $googleOauth;
    }

    /**
     * @Route("/{routing}", name="conversation_index", requirements={"routing"=".+"}, defaults={"routing": null}, methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal, CustomObject $customObject, SessionInterface $session) {
        // after the entire oauth process has happened we want to redirect to a page back on our site
        $session->set('after_oauth_redirect_url', $this->generateUrl('conversation_index', [
            'internalIdentifier' => $portal->getInternalIdentifier()
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        $googleProfile = null;
        if($session->has('access_token')) {
            $messages = $this->googleOauth->getMessages($session->get('access_token'));
            foreach($messages as $message) {
                $raw = $message->getRaw();
                $payload = $message->getPayload();
                $name = "Josh";
            }
        }

        return $this->render('conversation/index.html.twig', array(
            'portal' => $portal
        ));
    }
}