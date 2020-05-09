<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AclEntryRepository")
 */
class AclEntry
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mask;

    /**
     * @ORM\Column(type="boolean")
     */
    private $granting = true;

    /**
     * @ORM\Column(type="array")
     */
    private $grantingStrategy = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $objectIdentifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attributeIdentifier;

    /**
     * @ORM\ManyToOne(targetEntity="AclSecurityIdentity", inversedBy="aclEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $securityIdentity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMask(): ?int
    {
        return $this->mask;
    }

    public function setMask(?int $mask): self
    {
        $this->mask = $mask;

        return $this;
    }

    public function getGranting(): ?bool
    {
        return $this->granting;
    }

    public function setGranting(bool $granting): self
    {
        $this->granting = $granting;

        return $this;
    }

    public function getGrantingStrategy(): ?array
    {
        return $this->grantingStrategy;
    }

    public function setGrantingStrategy(array $grantingStrategy): self
    {
        $this->grantingStrategy = $grantingStrategy;

        return $this;
    }

    public function getObjectIdentifier(): ?string
    {
        return $this->objectIdentifier;
    }

    public function setObjectIdentifier(?string $objectIdentifier): self
    {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    public function getAttributeIdentifier(): ?string
    {
        return $this->attributeIdentifier;
    }

    public function setAttributeIdentifier(?string $attributeIdentifier): self
    {
        $this->attributeIdentifier = $attributeIdentifier;

        return $this;
    }

    public function getSecurityIdentity(): ?AclSecurityIdentity
    {
        return $this->securityIdentity;
    }

    public function setSecurityIdentity(?AclSecurityIdentity $securityIdentity): self
    {
        $this->securityIdentity = $securityIdentity;

        return $this;
    }
}
