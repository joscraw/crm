<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GmailThreadRepository")
 */
class GmailThread
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="GmailAccount", inversedBy="gmailThreads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gmailAccount;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $threadId;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GmailMessage", mappedBy="gmailThread", orphanRemoval=true)
     */
    private $gmailMessages;

    public function __construct()
    {
        $this->gmailMessages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGmailAccount(): ?GmailAccount
    {
        return $this->gmailAccount;
    }

    public function setGmailAccount(?GmailAccount $gmailAccount): self
    {
        $this->gmailAccount = $gmailAccount;

        return $this;
    }

    public function getThreadId(): ?string
    {
        return $this->threadId;
    }

    public function setThreadId(string $threadId): self
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * @return Collection|GmailMessage[]
     */
    public function getGmailMessages(): Collection
    {
        return $this->gmailMessages;
    }

    public function addGmailMessage(GmailMessage $gmailMessage): self
    {
        if (!$this->gmailMessages->contains($gmailMessage)) {
            $this->gmailMessages[] = $gmailMessage;
            $gmailMessage->setGmailThread($this);
        }

        return $this;
    }

    public function removeGmailMessage(GmailMessage $gmailMessage): self
    {
        if ($this->gmailMessages->contains($gmailMessage)) {
            $this->gmailMessages->removeElement($gmailMessage);
            // set the owning side to null (unless already changed)
            if ($gmailMessage->getGmailThread() === $this) {
                $gmailMessage->setGmailThread(null);
            }
        }

        return $this;
    }
}
