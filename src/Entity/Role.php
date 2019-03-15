<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 */
class Role
{

    use TimestampableEntity;

    /**
     * @Groups({"ROLES_FOR_DATATABLE"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"ROLES_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget a name for your brand new Role!")
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Don't forget to set Object Permissions!")
     * @ORM\Column(type="json")
     */
    private $objectPermissions = [];

    /**
     * @Assert\NotBlank(message="Don't forget to set System Permissions!")
     *
     * @ORM\Column(type="json")
     */
    private $systemPermissions = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="customRoles")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return mixed
     */
    public function getObjectPermissions()
    {
        return $this->objectPermissions;
    }

    /**
     * @param mixed $objectPermissions
     */
    public function setObjectPermissions($objectPermissions): void
    {
        $this->objectPermissions = $objectPermissions;
    }

    /**
     * @return mixed
     */
    public function getSystemPermissions()
    {
        return $this->systemPermissions;
    }

    /**
     * @param mixed $systemPermissions
     */
    public function setSystemPermissions($systemPermissions): void
    {
        $this->systemPermissions = $systemPermissions;
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

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addCustomRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeCustomRole($this);
        }

        return $this;
    }
}
