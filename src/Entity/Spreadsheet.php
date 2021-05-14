<?php

namespace App\Entity;

use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SpreadsheetRepository")
 */
class Spreadsheet extends Image
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $mappings = [];

    public function getPath()
    {
        return UploaderHelper::SPREADSHEET.'/'.$this->getFileName();
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

    public function getMappings(): ?array
    {
        return $this->mappings;
    }

    public function setMappings(?array $mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }
}
