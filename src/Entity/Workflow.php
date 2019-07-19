<?php

namespace App\Entity;

use App\Model\AbstractTrigger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 *
 * @ORM\EntityListeners({"App\EntityListener\WorkflowListener"})
 * @ORM\Entity(repositoryClass="App\Repository\WorkflowRepository")
 */
class Workflow
{
    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="workflows")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="string", length=255)
     */
    private $uid;

    /**
     * @Groups({"WORKFLOW"})
     * @var AbstractTrigger|[]
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $triggers;

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $actions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getTriggers()
    {
        return $this->triggers;
    }

    public function setTriggers($triggers): self
    {
        $this->triggers = $triggers;

        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function setActions($actions): self
    {
        $this->actions = $actions;

        return $this;
    }
}
