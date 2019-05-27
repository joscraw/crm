<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FormRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Form
{
    use TimestampableEntity;

    const REGULAR_FORM = 'REGULAR_FORM';
    const SUBMIT_ACTION_MESSAGE = 'MESSAGE';
    const SUBMIT_ACTION_REDIRECT = 'REDIRECT';

    /**
     * @Groups({"FORMS"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @Groups({"FORMS"})
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="forms")
     */
    private $customObject;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @Groups({"FORMS"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="forms")
     */
    private $portal;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255)
     */
    private $uid;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="boolean")
     */
    private $published = false;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="json", nullable=true)
     */
    private $draft = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $submissionCount = 0;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $submitAction = self::SUBMIT_ACTION_MESSAGE;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $submitMessage = 'Thanks for submitting the form.';

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $redirectUrl;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="boolean")
     */
    private $cookieTracking = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recaptcha = false;

    /**
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function setFormName()
    {
        if(empty($this->name)) {
            $this->name = sprintf('New form (%s)', date("M j, Y g:i:s A"));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(?Portal $portal): self
    {
        $this->portal = $portal;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getDraft(): ?array
    {
        return $this->draft;
    }

    public function setDraft(?array $draft): self
    {
        $this->draft = $draft;

        return $this;
    }

    public function getSubmissionCount(): ?int
    {
        return $this->submissionCount;
    }

    public function setSubmissionCount(?int $submissionCount): self
    {
        $this->submissionCount = $submissionCount;

        return $this;
    }

    public function incrementSubmissionCount() {
        $this->submissionCount++;
    }

    public function getSubmitAction(): ?string
    {
        return $this->submitAction;
    }

    public function setSubmitAction(?string $submitAction): self
    {
        $this->submitAction = $submitAction;

        return $this;
    }

    public function getSubmitMessage(): ?string
    {
        return $this->submitMessage;
    }

    public function setSubmitMessage(?string $submitMessage): self
    {
        $this->submitMessage = $submitMessage;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getCookieTracking(): ?bool
    {
        return $this->cookieTracking;
    }

    public function setCookieTracking(?bool $cookieTracking): self
    {
        $this->cookieTracking = $cookieTracking;

        return $this;
    }

    public function getRecaptcha(): ?bool
    {
        return $this->recaptcha;
    }

    public function setRecaptcha(bool $recaptcha): self
    {
        $this->recaptcha = $recaptcha;

        return $this;
    }
}
