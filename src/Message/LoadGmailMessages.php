<?php

namespace App\Message;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class LoadGmailMessages
 * @package App\Message
 */
class LoadGmailMessages
{
    /**
     * @var int
     */
    private $gmailId;

    public function __construct(int $gmailId)
    {
        $this->gmailId = $gmailId;
    }

    /**
     * @return int
     */
    public function getGmailId(): int
    {
        return $this->gmailId;
    }
}