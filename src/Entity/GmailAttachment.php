<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GmailAttachmentRepository")
 */
class GmailAttachment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $originalFileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mimeType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GmailMessage", inversedBy="gmailAttachments")
     */
    private $gmailMessage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileType;

    /**
     * @ORM\Column(type="integer")
     */
    private $fileSize;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $downloadUrl;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getOriginalFileName(): ?string
    {
        return $this->originalFileName;
    }

    public function setOriginalFileName(string $originalFileName): self
    {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getGmailMessage(): ?GmailMessage
    {
        return $this->gmailMessage;
    }

    public function setGmailMessage(?GmailMessage $gmailMessage): self
    {
        $this->gmailMessage = $gmailMessage;

        return $this;
    }
    
    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getAttachmentFilePath() {
        return UploaderHelper::ATTACHMENT . '/' . $this->getFileName();
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl(string $downloadUrl): self
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
    }
}
