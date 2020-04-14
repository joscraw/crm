<?php

namespace App\Entity;

use App\Message\WorkflowSendEmailActionMessage;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowSendEmailActionRepository")
 */
class WorkflowSendEmailAction extends WorkflowAction
{
    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    protected static $name = WorkflowAction::WORKFLOW_SEND_EMAIL_ACTION;

    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    protected static $description = 'Workflow action for sending an email.';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $toAddresses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    public function getHandlerMessage() {
        return new WorkflowSendEmailActionMessage($this->getId());
    }

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

    public function getMergeTags() {

        $mergeTags = [];
        $regex = '~\{([^}]*)\}~';
        preg_match_all($regex, $this->getBody(), $matches);
        $mergeTags = array_merge($mergeTags, $matches[0]);
        preg_match_all($regex, $this->getSubject(), $matches);
        $mergeTags = array_merge($mergeTags, $matches[0]);
        preg_match_all($regex, $this->getToAddresses(), $matches);
        $mergeTags = array_merge($mergeTags, $matches[0]);
        $mergeTags = array_unique($mergeTags);

        foreach($mergeTags as $key => $value) {
            $mergeTags[$key] = str_replace(["{", "}"], "", $value);
        }

        return $mergeTags;
    }

    public function getMergedBody($record) {
        $body = $this->body;
        foreach($record as $mergeTag => $value) {
            $body = str_replace(sprintf("{%s}", $mergeTag), $value, $body);
        }
        return $body;
    }

    public function getMergedToAddresses($record) {
        $toAddresses = $this->toAddresses;
        foreach($record as $mergeTag => $value) {
            $toAddresses = str_replace(sprintf("{%s}", $mergeTag), $value, $toAddresses);
        }
        return $toAddresses;
    }

    public function getMergedSubject($record) {
        $subject = $this->subject;
        foreach($record as $mergeTag => $value) {
            $subject = str_replace(sprintf("{%s}", $mergeTag), $value, $subject);
        }
        return $subject;
    }
}
