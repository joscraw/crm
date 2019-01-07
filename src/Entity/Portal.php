<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PortalRepository")
 */
class Portal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomObject", mappedBy="portal", cascade={"persist", "remove"})
     */
    private $customObjects;

    public function __construct()
    {
        $this->customObjects = new ArrayCollection();
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
}
