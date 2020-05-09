<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AclSecurityIdentityRepository")
 */
class AclSecurityIdentity
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
    private $identity;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AclEntry", mappedBy="securityIdentity", orphanRemoval=true, cascade={"persist"})
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

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

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
            $aclEntry->setSecurityIdentity($this);
        }

        return $this;
    }

    public function removeAclEntry(AclEntry $aclEntry): self
    {
        if ($this->aclEntries->contains($aclEntry)) {
            $this->aclEntries->removeElement($aclEntry);
            // set the owning side to null (unless already changed)
            if ($aclEntry->getSecurityIdentity() === $this) {
                $aclEntry->setSecurityIdentity(null);
            }
        }

        return $this;
    }
}
