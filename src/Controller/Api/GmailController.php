<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\GmailAttachment;
use App\Entity\GmailMessage;
use App\Entity\GmailThread;
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
use League\Flysystem\FilesystemInterface;
use PhpMimeMailParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @Route("/messages/attachments/{fileName}/download", name="gmail_download_message_attachment", methods={"GET"}, options = { "expose" = true })
     * @param $fileName
     * @param UploaderHelper $uploaderHelper
     * @param FilesystemInterface $privateUploadsFilesystem
     * @param FilesystemInterface $tmpDirectoryFilesystem
     * @return StreamedResponse
     */
    public function downloadMessageAttachment($fileName, UploaderHelper $uploaderHelper, FilesystemInterface $privateUploadsFilesystem, FilesystemInterface $tmpDirectoryFilesystem)
    {
        $gmailAttachment = $this->gmailAttachmentRepository->findOneBy([
           'fileName' => $fileName
        ]);

        /*$this->denyAccessUnlessGranted('download_attachment', $chatMessage);*/
        $response = new StreamedResponse(function() use ($gmailAttachment, $uploaderHelper, $privateUploadsFilesystem, $tmpDirectoryFilesystem) {
            $outputStream = fopen('php://output', 'wb');
            $stream = $uploaderHelper->readStream($gmailAttachment->getAttachmentFilePath(), false);
            if ($stream === false) {
                throw new \Exception(sprintf('Error opening stream for "%s"', $gmailAttachment->getAttachmentFilePath()));
            }
            stream_copy_to_stream($stream, $outputStream);
        });
        $response->headers->set('Content-Type', $gmailAttachment->getMimeType());
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $gmailAttachment->getOriginalFileName()
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * This endpoint is used to return the newest message for each thread. This is especially useful
     * for a sidebar/sidenav of the messages to see most recent and to then highlight the unread messages
     *
     * @Route("/newest-message-for-threads", name="gmail_newest_message_for_threads", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getNewestMessageForThreads(Portal $portal, Request $request)
    {
        $data = $this->gmailMessageRepository->getNewestForThreads($portal);

        return new JsonResponse([
            'data' => $data,
            'success' => true
        ]);
    }

    /**
     * This endpoint returns all the messages for a given thread
     *
     *
     * @Route("/threads/{threadId}/messages", name="gmail_messages_for_thread", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param $threadId
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMessagesForThread(Portal $portal, $threadId, Request $request)
    {
        $data = $this->gmailMessageRepository->getMessagesForThread($portal, $threadId);

        return new JsonResponse([
            'data' => $data,
            'success' => true
        ]);
    }

    /**
     * This endpoint returns all the messages for a given thread
     *
     *
     * @Route("/send-message", name="gmail_send_message", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function sendMessage(Portal $portal, Request $request)
    {
        // TODO Consider how you are going to need to be able to pass up a threadId so you can just send to a single thread
        // TODO consider how you are going to handle attachments
        $message = $this->gmailProvider->sendMessage($portal, $portal->getGmailAccount()->getGoogleToken());
        $message = $this->gmailProvider->getMessage($portal->getGmailAccount()->getPortal(), $portal->getGmailAccount()->getGoogleToken(), $message->getId());

        $parser = new Parser();
        $raw = $message->getRaw();
        $switched = str_replace(['-', '_'], ['+', '/'], $raw);
        $raw = base64_decode($switched);
        $parser->setText($raw);
        $sentTo = $parser->getHeader('to');
        $sentFrom = $parser->getHeader('from');
        $subject = $parser->getHeader('subject');
        $messageBody = $parser->getMessageBody('text');
        $arrayHeaders = $parser->getHeaders();

        $existingGmailThread = $this->gmailThreadRepository->findOneBy([
            'threadId' => $message->getThreadId()
        ]);
        if(!$existingGmailThread) {
            $thread = new GmailThread();
            $thread->setGmailAccount($portal->getGmailAccount());
            $thread->setThreadId($message->getThreadId());
            $this->entityManager->persist($thread);
        } else {
            $thread = $existingGmailThread;
        }

        // Create the message in the database
        $gmailMessage = new GmailMessage();
        $gmailMessage->setGmailThread($thread);
        $gmailMessage->setMessageId($message->getId());
        $gmailMessage->setSentTo($sentTo);
        $gmailMessage->setSentFrom($sentFrom);
        $gmailMessage->setSubject($subject);
        $gmailMessage->setMessageBody($messageBody);
        $gmailMessage->setInternalDate($message->getInternalDate());
        $gmailMessage->setThreadId($message->getThreadId());
        $gmailMessage->setHistoryId($message->getHistoryId());
        $this->entityManager->persist($gmailMessage);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }
}