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
     * @ORM\ManyToOne(targetEntity="App\Entity\AclObjectIdentity", inversedBy="aclEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $objectIdentity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AclSecurityIdentity", inversedBy="aclEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $securityIdentity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fieldName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mask;

    /**
     * @ORM\Column(type="boolean")
     */
    private $granting;

    /**
     * @ORM\Column(type="array")
     */
    private $grantingStrategy = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObjectIdentity(): ?AclObjectIdentity
    {
        return $this->objectIdentity;
    }

    public function setObjectIdentity(?AclObjectIdentity $objectIdentity): self
    {
        $this->objectIdentity = $objectIdentity;

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

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    public function setFieldName(?string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
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
}
