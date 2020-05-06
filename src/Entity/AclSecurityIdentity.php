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
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="aclSecurityIdentities")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="aclSecurityIdentities")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AclEntry", mappedBy="securityIdentity", orphanRemoval=true)
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

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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
