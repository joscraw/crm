<?php

namespace App\Controller\Api;

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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
 * @Route("/api/oauth")
 *
 */
class OauthController extends AbstractController
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
     * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
     * @Route("/google-authorization", name="oauth_google_authorization", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function googleAuthorization(Request $request) {
        return $this->redirect($this->googleOauth->getAuthUrl());
    }

    /**
     * @see https://github.com/googleapis/google-api-php-client/blob/master/examples/simple-file-upload.php
     * @Route("/google-redirect-code", name="oauth_google_redirect_code", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function googleRedirectCode(Request $request, SessionInterface $session) {
        if($request->query->has('code')) {
            $token = $this->googleOauth->getGoogleClient()->fetchAccessTokenWithAuthCode($request->query->get('code'));
            $session->set('access_token', $token);
        }
        return $this->redirect($session->get('after_oauth_redirect_url'));
    }
}