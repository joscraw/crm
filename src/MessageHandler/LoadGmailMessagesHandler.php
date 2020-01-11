<?php

namespace App\MessageHandler;

use App\Entity\GmailAccount;
use App\Entity\GmailMessage;
use App\Entity\GmailThread;
use App\Message\LoadGmailMessages;
use App\Repository\GmailMessageRepository;
use App\Repository\GmailAccountRepository;
use App\Repository\GmailThreadRepository;
use App\Service\GmailProvider;
use Doctrine\ORM\EntityManagerInterface;
use PhpMimeMailParser\Parser;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class LoadGmailMessagesHandler
 * @package App\MessageHandler
 */
class LoadGmailMessagesHandler implements MessageHandlerInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GmailAccountRepository
     */
    private $gmailRepository;

    /**
     * @var GmailThreadRepository
     */
    private $gmailThreadRepository;

    /**
     * @var GmailMessageRepository
     */
    private $gmailMessageRepository;

    /**
     * @var GmailProvider
     */
    private $gmailProvider;

    /**
     * LoadGmailMessagesHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param GmailAccountRepository $gmailRepository
     * @param GmailThreadRepository $gmailThreadRepository
     * @param GmailMessageRepository $gmailMessageRepository
     * @param GmailProvider $gmailProvider
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GmailAccountRepository $gmailRepository,
        GmailThreadRepository $gmailThreadRepository,
        GmailMessageRepository $gmailMessageRepository,
        GmailProvider $gmailProvider
    ) {
        $this->entityManager = $entityManager;
        $this->gmailRepository = $gmailRepository;
        $this->gmailThreadRepository = $gmailThreadRepository;
        $this->gmailMessageRepository = $gmailMessageRepository;
        $this->gmailProvider = $gmailProvider;
    }

    /**
     * @see https://symfonycasts.com/screencast/messenger
     * NOTES:
     * 1. Make sure every time you make a change to a handler you stop and restart the workers
     *
     * 2. To start the workers run ./bin/console messenger:consume -vv (for verbosity)
     *
     * 3. When needing to pass an entity to a handler just pass the ID of the entity
     * into the Message and query for it inside the handler. If you were to pass the whole entity
     * in and try to make a change to to it and call $entityManager->Flush()
     * Doctrine would not be managing it in it's IdentityMap and no changes would happen. If you called
     * $entityManager->persist() then $entityManager->Flush() then doctrine would end up creating a brand new entity!
     * This is not desired behavior as you just want to make changes to the entity that you passed in. The solution is simple!
     * Just pass the ID of the entity into the message object and then query for that entity Example:
     * $user = $this->userRepository->find($message->getUserId())
     *
     * 4. If the entity gets deleted from the db before the handler picks it up this can throw an error and halt your worker.
     * Make sure you check to make sure the entity exists after querying for it. Example: if($user) {//then perform your actions here}
     * If the entity is not found you have 2 options on what you can do. Option 1. Just return. if(!$user) {return;}. If you do this
     * then the message will be removed from the queue and will not retry. Option 2. You can throw an exception and then the message will
     * get retried later.
     *
     * 5. An aknowledged message means the message was handeled and removed from the queue. https://cl.ly/a796c7daa7e1 Even if you
     * return from the __invoke for whatever reason, it will remove the message from the queue and say it was aknowledged.
     *
     * @param LoadGmailMessages $message
     */
    public function __invoke(LoadGmailMessages $message)
    {
        /** @var GmailAccount $gmailAccount */
        $gmailAccount = $this->gmailRepository->find($message->getGmailId());
        if(!$gmailAccount) {
            if ($this->logger) {
                $this->logger->alert(sprintf('Gmail account %d was missing!', $message->getGmailId()));
            }
            return;
        }

        /** @var \Google_Service_Gmail_ListHistoryResponse $historyList */
        $historyList = $this->gmailProvider->getHistoryList($gmailAccount->getPortal(), $gmailAccount->getGoogleToken(), $gmailAccount->getCurrentHistoryId());

        // Add messages to the database
        $this->addMessagesFromHistoryList($historyList, $gmailAccount);

        // We aren't running this now as we probably don't want to delete messages from the CRM when they are deleted from GMAIL
        //$this->removeMessagesFromHistoryList($historyList, $gmailAccount);

        // Go ahead and update the main current history id. this ensures we aren't pulling duplicate messages next time this handler runs
        $mailboxHistoryId = $historyList->getHistoryId();
        $gmailAccount->setCurrentHistoryId($mailboxHistoryId);
        $this->entityManager->flush();
        echo sprintf("Gmail messages handler successfully completed.");
    }

    /**
     * @param \Google_Service_Gmail_ListHistoryResponse $historyList
     * @param GmailAccount $gmailAccount
     */
    private function addMessagesFromHistoryList(\Google_Service_Gmail_ListHistoryResponse $historyList, GmailAccount $gmailAccount) {
        if(empty($historyList['history'])) {
            return;
        }

        /** @var \Google_Service_Gmail_History $history */
        foreach($historyList['history'] as $history) {
            $messagesAdded = $history->getMessagesAdded();

            /** @var \Google_Service_Gmail_HistoryMessageAdded $messageAdded */
            foreach($messagesAdded as $messageAdded) {

                /** @var \Google_Service_Gmail_Message $message */
                $message = $messageAdded->getMessage();
                $messageId = $message->getId();

                // We need the all the message data so pull the full message from the API
                $message = $this->gmailProvider->getMessage($gmailAccount->getPortal(), $gmailAccount->getGoogleToken(), $messageId);

                // Parse the message from the raw data
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

                // Check to see if a thread exists in the database for this message
                // If not go ahead and create a brand new thread in the db
                /** @var GmailThread $thread */
                $existingGmailThread = $this->gmailThreadRepository->findOneBy([
                    'threadId' => $message->getThreadId()
                ]);
                if(!$existingGmailThread) {
                    $thread = new GmailThread();
                    $thread->setGmailAccount($gmailAccount);
                    $thread->setThreadId($message->getThreadId());
                    $this->entityManager->persist($thread);
                } else {
                    $thread = $existingGmailThread;
                }

                // Create the message in the database
                $gmailMessage = new GmailMessage();
                $gmailMessage->setGmailThread($thread);
                $gmailMessage->setMessageId($messageId);
                $gmailMessage->setSentTo($sentTo);
                $gmailMessage->setSentFrom($sentFrom);
                $gmailMessage->setSubject($subject);
                $gmailMessage->setMessageBody($messageBody);
                $gmailMessage->setInternalDate($message->getInternalDate());
                $this->entityManager->persist($gmailMessage);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param \Google_Service_Gmail_ListHistoryResponse $historyList
     * @param GmailAccount $gmailAccount
     */
    private function removeMessagesFromHistoryList(\Google_Service_Gmail_ListHistoryResponse $historyList, GmailAccount $gmailAccount) {
        if(empty($historyList['history'])) {
            return;
        }

        /** @var \Google_Service_Gmail_History $history */
        foreach($historyList['history'] as $history) {
            $messagesDeleted = $history->getMessagesDeleted();

            /** @var \Google_Service_Gmail_HistoryMessageDeleted $messageDeleted */
            foreach($messagesDeleted as $messageDeleted) {

                /** @var \Google_Service_Gmail_Message $message */
                $message = $messageDeleted->getMessage();
                $messageId = $message->getId();
                $messageToRemove = $this->gmailMessageRepository->findOneBy([
                   'messageId' => $messageId
                ]);
                if($messageToRemove) {
                    $this->entityManager->remove($messageToRemove);
                }
                $this->entityManager->flush();
            }
        }
    }
}