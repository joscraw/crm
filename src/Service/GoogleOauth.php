<?php

namespace App\Service;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Profile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GoogleOauth
{
    /**
     * @var KernelInterface
     */
    private $appKernel;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var Google_Client
     */
    private $googleClient;

    /**
     * @var Google_Service_Gmail
     */
    private $googleServiceGmail;

    /**
     * GoogleOauth constructor.
     * @param KernelInterface $appKernel
     * @param RouterInterface $router
     * @throws \Google_Exception
     */
    public function __construct(KernelInterface $appKernel, RouterInterface $router, SessionInterface $session)
    {
        $this->appKernel = $appKernel;
        $this->router = $router;
        $this->session = $session;
        $projectDirectory = $this->appKernel->getProjectDir();
        $oauthCredentials = $projectDirectory . '/client_secret_google.json';
        $redirectUri = $this->router->generate('oauth_google_redirect_code', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->googleClient = new Google_Client();
        $this->googleClient->setAuthConfig($oauthCredentials);
        $this->googleClient->setRedirectUri($redirectUri);
        $this->googleClient->addScope([
            'https://mail.google.com/',
        ]);
    }

    /**
     * @return string
     */
    public function getAuthUrl() {
        return $this->googleClient->createAuthUrl();
    }

    /**
     * @param bool $accessToken
     * @return bool|Google_Client
     */
    public function getGoogleClient($accessToken = false)
    {
        if(!$accessToken) {
            return $this->googleClient;
        }
        $this->googleClient->setAccessToken($accessToken);
        if($this->googleClient->isAccessTokenExpired()) {
            $refreshToken = $accessToken['refresh_token'];
            $this->googleClient->refreshToken($refreshToken);
        }
        return $this->googleClient;
    }

    /**
     * @param $accessToken
     * @return Google_Service_Gmail_Profile
     */
    public function getProfile($accessToken) {
        $googleClient = $this->getGoogleClient($accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $profile = $this->googleServiceGmail->users->getProfile('me');
        return $profile;
    }

    /**
     * @param $accessToken
     * @return \Google_Service_Gmail_ListMessagesResponse
     */
    public function getMessages($accessToken) {
        $googleClient = $this->getGoogleClient($accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $messages = $this->googleServiceGmail->users_messages->listUsersMessages('me');
        return $messages;
    }
}