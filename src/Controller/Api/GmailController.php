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
use App\Service\GmailProvider;
use App\Service\MessageGenerator;
use App\Service\UploaderHelper;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Gmail;
use PhpMimeMailParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
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
 * Class GoogleController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/api/gmail")
 *
 */
class GmailController extends AbstractController
{
    use ServiceHelper;

    /**
     * @Route("/message-list", name="google_message_list", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function messageList(Portal $portal, Request $request) {
        $user = $this->getUser();
        // make sure the portal has a google access token
        if(!empty($portal->getGoogleToken())) {
            $messages = $this->gmailProvider->getMessageList($portal, $portal->getGoogleToken());

            foreach($messages as $message) {

                $message = $this->gmailProvider->getMessage($portal, $portal->getGoogleToken(), $message->getId());
                $parser = new Parser();
                $raw = $message->getRaw();
                $switched = str_replace(['-', '_'], ['+', '/'], $raw);
                $raw = base64_decode($switched);
                $parser->setText($raw);
                $rawHeaderTo = $parser->getHeader('to');
                $subject = $parser->getHeader('subject');
                $text = $parser->getMessageBody('text');
                $html = $parser->getMessageBody('html');    // get body in HTML format
                $stringHeaders = $parser->getHeadersRaw();
                $arrayHeaders = $parser->getHeaders();
                $parser->saveAttachments('/var/www/attachments');
            }
        }
        return new JsonResponse(
            [
                'success' => true,
                'data' => $messages
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/message/{messageId}", name="google_get_message", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param $messageId
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getMessage(Portal $portal, $messageId, Request $request) {
        $user = $this->getUser();
        // make sure the portal has a google access token
        if(!empty($portal->getGoogleToken())) {
            $message = $this->gmailProvider->getMessage($portal, $portal->getGoogleToken(), $messageId);
            $parser = new Parser();
            $raw = $message->getRaw();
            $switched = str_replace(['-', '_'], ['+', '/'], $raw);
            $raw = base64_decode($switched);
            $parser->setText($raw);
            $rawHeaderTo = $parser->getHeader('to');
            $subject = $parser->getHeader('subject');
            $text = $parser->getMessageBody('text');
            $html = $parser->getMessageBody('html');    // get body in HTML format
            $stringHeaders = $parser->getHeadersRaw();
            $arrayHeaders = $parser->getHeaders();
            $parser->saveAttachments('/var/www/attachments');

            return new JsonResponse(
                [
                    'success' => true,
                    'data' => $message
                ],
                Response::HTTP_OK
            );
        }
        return new JsonResponse(
            [
                'success' => false,
            ],
            Response::HTTP_OK
        );
    }

    public function getChattedWithUsers() {
        // todo we need to get users that we've chatted with
        //  so this can go in the sidebar
        //  https://cl.ly/4a7b285a72db
        //  hubspot doesn't pull in all of your chats from your inbox. Just the ones that you
        //  initiate otherwise your entire chat would be cluttered right away with messages you don't want or need
    }


    /**
     * @Route("/thread-list", name="gmail_thread_list", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @see https://github.com/php-mime-mail-parser/php-mime-mail-parser
     */
    public function threadList(Portal $portal, Request $request) {
        // todo we need to use caching here so we respond back with the
        $user = $this->getUser();
        // make sure the portal has a google access token
        if(!empty($portal->getGoogleToken())) {
            $threads = $this->gmailProvider->getThreadList($portal, $portal->getGoogleToken());
            $data = [];
            foreach($threads as $thread) {
                $thread = $this->gmailProvider->getThread($portal, $portal->getGoogleToken(), $thread->getId());
                $messages = $thread->getMessages();
                foreach($messages as $message) {
                    $message = $this->gmailProvider->getMessage($portal, $portal->getGoogleToken(), $message->getId());
                    $parser = new Parser();
                    $raw = $message->getRaw();
                    $switched = str_replace(['-', '_'], ['+', '/'], $raw);
                    $raw = base64_decode($switched);
                    $parser->setText($raw);
                    $arrayHeaders = $parser->getHeaders();
                    $attachments = $parser->saveAttachments(sprintf("%s/%s", $this->uploadsPath, UploaderHelper::ATTACHMENT), true, Parser::ATTACHMENT_DUPLICATE_SUFFIX);
                    $attachmentUrls = [];
                    // let's return the actual web path URL to the attachment in the data response
                    foreach($attachments as $attachment) {
                        $attachmentUrls[] = sprintf("%s/%s",
                            $this->getFullQualifiedBaseUrl(),
                            str_replace('/var/www/public/', '', $attachment)
                        );
                    }
                    $data[$thread->getId()][] = [
                        'to' =>   $parser->getHeader('to'),
                        'from' => $parser->getHeader('from'),
                        'subject' => $parser->getHeader('subject'),
                        'text' => $parser->getMessageBody('text'),
                        'attachments' => $attachmentUrls
                    ];
                }
            }
            return new JsonResponse(
                [
                    'success' => true,
                    'data' => $data
                ],
                Response::HTTP_OK
            );
        }
        return new JsonResponse(
            [
                'success' => false,
            ],
            Response::HTTP_OK
        );
    }
}