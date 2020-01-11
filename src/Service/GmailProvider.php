<?php

namespace App\Service;

use App\Entity\Portal;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Profile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GmailProvider
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * GoogleOauth constructor.
     * @param KernelInterface $appKernel
     * @param RouterInterface $router
     * @throws \Google_Exception
     */
    public function __construct(KernelInterface $appKernel, RouterInterface $router, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->appKernel = $appKernel;
        $this->router = $router;
        $this->session = $session;
        $this->entityManager = $entityManager;
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
     * @param null $scope
     * @param array $config
     * @return string
     */
    public function getAuthUrl($scope = null, $config = []) {
        return $this->googleClient->createAuthUrl();
    }

    /**
     * Returns the google client and refreshes the access token if need be
     *
     * @param Portal|null $portal
     * @param bool $accessToken
     * @return bool|Google_Client
     */
    public function getGoogleClient(Portal $portal = null, $accessToken = false)
    {
        if(!$accessToken) {
            return $this->googleClient;
        }
        $this->googleClient->setAccessToken($accessToken);
        if($this->googleClient->isAccessTokenExpired()) {
            $refreshToken = $accessToken['refresh_token'];
            $newAccessToken = $this->googleClient->refreshToken($refreshToken);
            $gmailAccount = $portal->getGmailAccount();
            $gmailAccount->setGoogleToken($newAccessToken);
            $this->entityManager->persist($gmailAccount);
            $this->entityManager->flush();
        }
        return $this->googleClient;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @return Google_Service_Gmail_Profile
     */
    public function getProfile(Portal $portal, $accessToken) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $profile = $this->googleServiceGmail->users->getProfile('me');
        return $profile;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @return \Google_Service_Gmail_ListMessagesResponse
     */
    public function getMessageList(Portal $portal, $accessToken) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $messages = $this->googleServiceGmail->users_messages->listUsersMessages('me', ['labelIds' => 'INBOX', 'maxResults' => 10, 'q' => 'category:primary']);
        return $messages;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @param $messageId
     * @return \Google_Service_Gmail_Message
     */
    public function getMessage(Portal $portal, $accessToken, $messageId) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        /*$optParams['format'] = 'full';*/
        $optParams['format'] = 'raw';
        $message = $this->googleServiceGmail->users_messages->get('me', $messageId, $optParams);
        return $message;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @param int $maxResults
     * @return \Google_Service_Gmail_ListThreadsResponse
     */
    public function getThreadList(Portal $portal, $accessToken, $maxResults = 20) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $threads = $this->googleServiceGmail->users_threads->listUsersThreads('me', ['labelIds' => 'INBOX', 'maxResults' => $maxResults, 'q' => 'category:primary']);
        return $threads;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @param $threadId
     * @return \Google_Service_Gmail_Thread
     */
    public function getThread(Portal $portal, $accessToken, $threadId) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        /*$optParams['format'] = 'full';*/
        /*$optParams['format'] = 'raw';*/
        $thread = $this->googleServiceGmail->users_threads->get('me', $threadId);
        return $thread;
    }

    /**
     * @param Portal $portal
     * @param $accessToken
     * @param $startHistoryId
     * @return \Google_Service_Gmail_ListHistoryResponse
     */
    public function getHistoryList(Portal $portal, $accessToken, $startHistoryId) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);
        $historyList = $this->googleServiceGmail->users_history->listUsersHistory('me', ['labelId' => 'INBOX', 'historyTypes' => ['messageAdded', 'messageDeleted'], 'startHistoryId' => $startHistoryId]);
        return $historyList;
    }
}