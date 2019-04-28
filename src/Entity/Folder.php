<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FolderRepository")
 */
class Folder
{
    use TimestampableEntity;

    const LIST_FOLDER = 'LIST_FOLDER';
    const REPORT_FOLDER = 'REPORT_FOLDER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Folder", inversedBy="childFolders")
     */
    private $parentFolder;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Folder", mappedBy="parentFolder")
     */
    private $childFolders;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="folders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MarketingList", mappedBy="folder")
     */
    private $marketingLists;

    public function __construct()
    {
        $this->childFolders = new ArrayCollection();
        $this->marketingLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentFolder(): ?self
    {
        return $this->parentFolder;
    }

    public function setParentFolder(?self $parentFolder): self
    {
        $this->parentFolder = $parentFolder;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildFolders(): Collection
    {
        return $this->childFolders;
    }

    public function addChildFolder(self $childFolder): self
    {
        if (!$this->childFolders->contains($childFolder)) {
            $this->childFolders[] = $childFolder;
            $childFolder->setParentFolder($this);
        }

        return $this;
    }

    public function removeChildFolder(self $childFolder): self
    {
        if ($this->childFolders->contains($childFolder)) {
            $this->childFolders->removeElement($childFolder);
            // set the owning side to null (unless already changed)
            if ($childFolder->getParentFolder() === $this) {
                $childFolder->setParentFolder(null);
            }
        }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
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

    /**
     * @return Collection|MarketingList[]
     */
    public function getMarketingLists(): Collection
    {
        return $this->marketingLists;
    }

    public function addMarketingList(MarketingList $marketingList): self
    {
        if (!$this->marketingLists->contains($marketingList)) {
            $this->marketingLists[] = $marketingList;
            $marketingList->setFolder($this);
        }

        return $this;
    }

    public function removeMarketingList(MarketingList $marketingList): self
    {
        if ($this->marketingLists->contains($marketingList)) {
            $this->marketingLists->removeElement($marketingList);
            // set the owning side to null (unless already changed)
            if ($marketingList->getFolder() === $this) {
                $marketingList->setFolder(null);
            }
        }

        return $this;
    }
}
