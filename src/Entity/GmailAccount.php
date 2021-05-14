<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GmailAccountRepository")
 */
class GmailAccount
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Portal", inversedBy="gmailAccount", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GmailThread", mappedBy="gmailAccount", orphanRemoval=true)
     */
    private $gmailThreads;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currentHistoryId;

    /**
     * @ORM\Column(type="array")
     */
    private $googleToken = [];

    public function __construct()
    {
        $this->gmailThreads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    /**
     * @return Collection|GmailThread[]
     */
    public function getGmailThreads(): Collection
    {
        return $this->gmailThreads;
    }

    public function addGmailThread(GmailThread $gmailThread): self
    {
        if (!$this->gmailThreads->contains($gmailThread)) {
            $this->gmailThreads[] = $gmailThread;
            $gmailThread->setGmail($this);
        }

        return $this;
    }

    public function removeGmailThread(GmailThread $gmailThread): self
    {
        if ($this->gmailThreads->contains($gmailThread)) {
            $this->gmailThreads->removeElement($gmailThread);
            // set the owning side to null (unless already changed)
            if ($gmailThread->getGmail() === $this) {
                $gmailThread->setGmail(null);
            }
        }

        return $this;
    }

    public function getCurrentHistoryId(): ?string
    {
        return $this->currentHistoryId;
    }

    public function setCurrentHistoryId(string $currentHistoryId): self
    {
        $this->currentHistoryId = $currentHistoryId;

        return $this;
    }

    public function getGoogleToken(): ?array
    {
        return $this->googleToken;
    }

    public function setGoogleToken(array $googleToken): self
    {
        $this->googleToken = $googleToken;

        return $this;
    }
}
