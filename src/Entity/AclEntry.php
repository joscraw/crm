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
    private $granting = true;

    /**
     * @ORM\Column(type="array")
     */
    private $grantingStrategy = [];

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $objectIdentifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $classType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securityIdentity;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getObjectIdentifier(): ?int
    {
        return $this->objectIdentifier;
    }

    public function setObjectIdentifier(?int $objectIdentifier): self
    {
        $this->objectIdentifier = $objectIdentifier;

        return $this;
    }

    public function getClassType(): ?string
    {
        return $this->classType;
    }

    public function setClassType(?string $classType): self
    {
        $this->classType = $classType;

        return $this;
    }

    public function getSecurityIdentity(): ?string
    {
        return $this->securityIdentity;
    }

    public function setSecurityIdentity(?string $securityIdentity): self
    {
        $this->securityIdentity = $securityIdentity;

        return $this;
    }
}
