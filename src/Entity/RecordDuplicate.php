<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecordDuplicateRepository")
 */
class RecordDuplicate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="recordDuplicates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $properties = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Record", inversedBy="recordDuplicates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conflictingRecord;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function setProperties(?array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function getConflictingRecord(): ?Record
    {
        return $this->conflictingRecord;
    }

    public function setConflictingRecord(?Record $conflictingRecord): self
    {
        $this->conflictingRecord = $conflictingRecord;

        return $this;
    }
}
