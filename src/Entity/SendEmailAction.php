<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SendEmailActionRepository")
 */
class SendEmailAction extends Action
{
    const DYNAMIC_USERS_TYPE = 'TO_MATCHING_USERS_TYPE';
    const STATIC_USER_TYPE = 'TO_MATCHING_USERS_TYPE';

    /**
     * @Groups({"WORKFLOW_ACTION", "MD5_HASH_WORKFLOW"})
     * @var string
     */
    protected $name = Action::SEND_EMAIL_ACTION;

    /**
     * @Groups({"WORKFLOW_ACTION", "MD5_HASH_WORKFLOW"})
     * @var string
     */
    protected $description = 'Send email';

    /**
     * @Groups({"WORKFLOW_ACTION", "MD5_HASH_WORKFLOW"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $toAddresses;

    /**
     * @Groups({"WORKFLOW_ACTION", "MD5_HASH_WORKFLOW"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @Groups({"WORKFLOW_ACTION", "MD5_HASH_WORKFLOW"})
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type = self::STATIC_USER_TYPE;

    public function getToAddresses(): ?string
    {
        return $this->toAddresses;
    }

    public function setToAddresses(?string $toAddresses): self
    {
        $this->toAddresses = $toAddresses;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

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
}
