<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AclLockRepository")
 */
class AclLock
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
    private $objectIdentifier;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $classType;

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
}
