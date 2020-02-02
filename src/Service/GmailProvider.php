<?php

namespace App\Service;

use App\Entity\Portal;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Google_Service_Gmail_MessagePartBody;
use Google_Service_Gmail_MessagePartHeader;
use Google_Service_Gmail_Profile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

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
     * @var \Twig\Environment $templating
     */
    private $templating;

    /**
     * GoogleOauth constructor.
     * @param KernelInterface $appKernel
     * @param RouterInterface $router
     * @param SessionInterface $session
     * @param EntityManagerInterface $entityManager
     * @param \Twig\Environment $templating
     * @throws \Google_Exception
     */
    public function __construct(KernelInterface $appKernel, RouterInterface $router, SessionInterface $session, EntityManagerInterface $entityManager, \Twig\Environment $templating)
    {
        $this->appKernel = $appKernel;
        $this->router = $router;
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->templating = $templating;
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
       /* $optParams['format'] = 'full';
        $optParams['format'] = 'raw';*/

        $optParams['format'] = 'metadata';
        $thread = $this->googleServiceGmail->users_threads->get('me', $threadId, $optParams);
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

    /**
     * @param Portal $portal
     * @param $accessToken
     * @return \Google_Service_Gmail_Message
     */
    public function sendMessage(Portal $portal, $accessToken) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);

        $message = (new \Swift_Message('Here is my subject'))
            ->setFrom('cultured44@gmail.com')
            ->setTo(['joshcrawmer4@yahoo.com' => 'Test Name'])
            ->setContentType('text/plain')
            ->setCharset('utf-8')
            ->setBody('Here is my body');

        $mime = $this->base64url_encode($message->toString());


        $msg = new Google_Service_Gmail_Message();
        $msg->setRaw($mime);
        //The special value **me** can be used to indicate the authenticated user.
        return $this->googleServiceGmail->users_messages->send("me", $msg);
    }

    /**
     * 1. For email clients to collapse the threads you need to format the response like so: https://cl.ly/66dad418dcdd
     *
     * 2. If you send the same exact message as before some email clients such as yahoo
     *  won't pull it in and display it in the client since it was the exact same as the last message.
     *
     *
     * @see https://wesmorgan.blogspot.com/2012/07/understanding-email-headers-part-ii.html
     * @see https://symfony.com/doc/current/mailer.html
     * @see https://symfony.com/blog/new-in-symfony-4-3-mime-component
     * @see https://symfony.com/doc/current/components/mime.html
     *
     * TODO Study the below link so you can see how to append thread messages to reply
     * @see https://stackoverflow.com/questions/57377694/how-to-append-thread-messages-while-reply-so-that-new-user-can-see-previous-conv
     * Also note that the clients look for a line break. So make sure to put the original message body in a <div> or precede it with a <br> for html
     * emails or a \n for text/plain emails for the original thread message to be collapsed
     *
     * @param Portal $portal
     * @param $accessToken
     * @param $subject
     * @param $threadId
     * @param $arrayHeaders
     * @param $messageBody
     * @param $parsedMessageBody
     * @return \Google_Service_Gmail_Message
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendMessage2(Portal $portal, $accessToken, $threadId, $arrayHeaders, $messageBody, $parsedTextMessageBody, $parsedHtmlMessageBody) {
        $googleClient = $this->getGoogleClient($portal, $accessToken);
        $this->googleServiceGmail = new Google_Service_Gmail($googleClient);

        $references = !empty($arrayHeaders['references']) ? $arrayHeaders['references'] . ' ' . $arrayHeaders['message-id'] : $arrayHeaders['message-id'];
        $references = explode(' ', $references);
        foreach ($references as $key => $reference){
            $references[$key]  = str_replace(['>', '<'], '', $reference);
        }

        $potentialRecipients = $arrayHeaders['to'] . ', ' . $arrayHeaders['from'];
        $potentialRecipients = mailparse_rfc822_parse_addresses($potentialRecipients);
        $profile = $this->getProfile($portal, $accessToken);

        $addresses = [];
        foreach($potentialRecipients as $key => $potentialRecipient) {
            // We don't want to send to our own address so skip that email!
            if($potentialRecipient['address'] === $profile->getEmailAddress()) {
                continue;
            }
            $addresses[] = new Address($potentialRecipient['address'], $potentialRecipient['display']);
        }

        $headers = (new Headers())
            // Gmail is going to automatically use the from address of the authenticated account
            // so you can just throw whatever here for the From address. But you can't omit it as
            // it's necessary to send an email
            ->addMailboxListHeader('From', [new Address('test@example.com', 'Test Example')])
            ->addMailboxListHeader('To', $addresses)
            ->addTextHeader('Subject', $arrayHeaders['subject'])  //'Re: ' .
            ->addIdHeader('In-Reply-To', str_replace(['>', '<'], '', $arrayHeaders['message-id']))
            ->addIdHeader('References', $references);

        $dateTime = new \DateTime(
            'now'
        );
        $date = sprintf("On %s, %s wrote:", $dateTime->format('l, F j, Y, h:i:s A T'), $arrayHeaders['from']);

        $textContent = new TextPart($this->templating->render('email/gmailMessage.txt.twig', ['messageBody' => $messageBody, 'parsedMessageBody' => $parsedTextMessageBody, 'date' => $date ] ), 'utf-8', 'plain', '8bit');
        $htmlContent = new TextPart($this->templating->render('email/gmailMessage.html.twig', ['messageBody' => $messageBody, 'parsedMessageBody' => $parsedHtmlMessageBody, 'date' => $date ] ), 'utf-8', 'html', 'quoted-printable');
        $body = new AlternativePart($textContent, $htmlContent);
        $email = new Message($headers, $body);
        $mime = $this->base64url_encode($email->toString());
        $msg = new Google_Service_Gmail_Message();
        $msg->setThreadId($threadId);
        $msg->setRaw($mime);
        //The special value **me** can be used to indicate the authenticated user.
        return $this->googleServiceGmail->users_messages->send("me", $msg);
    }

    private function base64url_encode($mime) {
        return rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
    }

}