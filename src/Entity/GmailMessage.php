<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GmailMessageRepository")
 */
class GmailMessage
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GmailThread", inversedBy="gmailMessages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gmailThread;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $messageId;

    /**
     * @ORM\Column(type="text")
     */
    private $sentTo;

    /**
     * @ORM\Column(type="text")
     */
    private $sentFrom;

    /**
     * @ORM\Column(type="text")
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $messageBody;

    /**
     * @ORM\Column(type="bigint")
     */
    private $internalDate;

    /**
     * We aren't checking to see if it's been read in the Gmail client, just our platform.
     * If it's been read in our CRM we aren't updating it in Gmail. If it's been read in Gmail
     * we aren't updating this in our CRM. This can be revisited down the road.
     * @ORM\Column(type="boolean")
     */
    private $isRead = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGmailThread(): ?GmailThread
    {
        return $this->gmailThread;
    }

    public function setGmailThread(?GmailThread $gmailThread): self
    {
        $this->gmailThread = $gmailThread;

        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getSentTo(): ?string
    {
        return $this->sentTo;
    }

    public function setSentTo(string $sentTo): self
    {
        $this->sentTo = $sentTo;

        return $this;
    }

    public function getSentFrom(): ?string
    {
        return $this->sentFrom;
    }

    public function setSentFrom(string $sentFrom): self
    {
        $this->sentFrom = $sentFrom;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getMessageBody(): ?string
    {
        return $this->messageBody;
    }

    public function setMessageBody(string $messageBody): self
    {
        $this->messageBody = $messageBody;

        return $this;
    }

    public function getInternalDate(): ?string
    {
        return $this->internalDate;
    }

    public function setInternalDate(string $internalDate): self
    {
        $this->internalDate = $internalDate;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}
