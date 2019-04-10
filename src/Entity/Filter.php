<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;


/**
 * @CustomAssert\FilterNameAlreadyExists()
 * @ORM\Entity(repositoryClass="App\Repository\FilterRepository")
 */
class Filter
{
    use TimestampableEntity;

    const RECORD_FILTER = 'RECORD_FILTER';

    /**
     * @Groups({"SAVED_FILTERS"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"SAVED_FILTERS"})
     *
     * @ORM\Column(type="json")
     */
    private $customFilters = [];

    /**
     * @ORM\Column(type="text")
     */
    private $query;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="filters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @Groups({"SAVED_FILTERS"})
     *
     * @Assert\NotBlank(message="Don't forget a name for your super cool sweeeeet Filter!")
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomFilters(): ?array
    {
        return $this->customFilters;
    }

    public function setCustomFilters(array $customFilters): self
    {
        $this->customFilters = $customFilters;

        return $this;
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

    public function getPortal(): ?Portal
    {
        return $this->portal;
    }

    public function setPortal(?Portal $portal): self
    {
        $this->portal = $portal;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
