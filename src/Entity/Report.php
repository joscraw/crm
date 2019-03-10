<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 */
class Report
{

    use TimestampableEntity;

    /**
     * @Groups({"REPORT"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"REPORT"})
     *
     * @ORM\Column(type="text")
     */
    private $query;

    /**
     * @Groups({"REPORT"})
     *
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @Groups({"REPORT"})
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customObject;

    /**
     * @Groups({"REPORT"})
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @Groups({"REPORT"})
     *
     * @ORM\Column(type="json")
     */
    private $columnOrder = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
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

    public function getColumnOrder(): ?array
    {
        return $this->columnOrder;
    }

    public function setColumnOrder(array $columnOrder): self
    {
        $this->columnOrder = $columnOrder;

        return $this;
    }
}
