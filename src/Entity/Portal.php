<?php

namespace App\Entity;

use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PortalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Portal
{
    use RandomStringGenerator;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $internalIdentifier;

    /**
     * @ORM\PrePersist
     */
    public function setInternalIdentifierValue()
    {
        if(!$this->internalIdentifier) {
            $this->internalIdentifier = $this->generateRandomNumber(10);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomObject", mappedBy="portal", cascade={"remove"})
     */
    private $customObjects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Report", mappedBy="portal", orphanRemoval=true)
     */
    private $reports;

    public function __construct()
    {
        $this->customObjects = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    /**
     * @return Collection|CustomObject[]
     */
    public function getCustomObjects(): Collection
    {
        return $this->customObjects;
    }

    public function addCustomObject(CustomObject $customObject): self
    {
        if (!$this->customObjects->contains($customObject)) {
            $this->customObjects[] = $customObject;
            $customObject->setPortal($this);
        }

        return $this;
    }

    public function removeCustomObject(CustomObject $customObject): self
    {
        if ($this->customObjects->contains($customObject)) {
            $this->customObjects->removeElement($customObject);
            // set the owning side to null (unless already changed)
            if ($customObject->getPortal() === $this) {
                $customObject->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalIdentifier(): ?string
    {
        return $this->internalIdentifier;
    }

    /**
     * @param $internalIdentifier
     * @return $this
     */
    public function setInternalIdentifier(string $internalIdentifier): self
    {
        $this->internalIdentifier = $internalIdentifier;

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setPortal($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getPortal() === $this) {
                $report->setPortal(null);
            }
        }

        return $this;
    }


}
