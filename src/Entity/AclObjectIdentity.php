<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AclObjectIdentityRepository")
 */
class AclObjectIdentity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $objectIdentifier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $classType;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AclEntry", mappedBy="objectIdentity")
     */
    private $aclEntries;

    public function __construct()
    {
        $this->aclEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectIdentifier(): ?int
    {
        return $this->objectIdentifier;
    }

    public function setObjectIdentifier(int $objectIdentifier): self
    {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    public function getClassType(): ?string
    {
        return $this->classType;
    }

    public function setClassType(string $classType): self
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     * @return Collection|AclEntry[]
     */
    public function getAclEntries(): Collection
    {
        return $this->aclEntries;
    }

    public function addAclEntry(AclEntry $aclEntry): self
    {
        if (!$this->aclEntries->contains($aclEntry)) {
            $this->aclEntries[] = $aclEntry;
            $aclEntry->setObjectIdentity($this);
        }

        return $this;
    }

    public function removeAclEntry(AclEntry $aclEntry): self
    {
        if ($this->aclEntries->contains($aclEntry)) {
            $this->aclEntries->removeElement($aclEntry);
            // set the owning side to null (unless already changed)
            if ($aclEntry->getObjectIdentity() === $this) {
                $aclEntry->setObjectIdentity(null);
            }
        }

        return $this;
    }
}
