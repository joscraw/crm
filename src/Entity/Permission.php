<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PermissionRepository")
 */
class Permission
{
    use TimestampableEntity;

    public static $permissions = [
        [
            'scope' => 'create:custom_objects',
            'description' => 'create custom objects',
        ],
        [
            'scope' => 'update:custom_objects',
            'description' => 'update custom objects'
        ],
        [
            'scope' => 'read:custom_objects',
            'description' => 'read custom objects',
        ],
        [
            'scope' => 'delete:custom_objects',
            'description' => 'delete custom objects'
        ],
        [
            'scope' => 'create:scopes',
            'description' => 'create scopes'
        ],
        [
            'scope' => 'update:scopes',
            'description' => 'update scopes'
        ],
        [
            'scope' => 'read:scopes',
            'description' => 'read scopes'
        ],
        [
            'scope' => 'delete:scopes',
            'description' => 'delete scopes'
        ]
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $scope;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", mappedBy="permissions")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addPermission($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            $role->removePermission($this);
        }

        return $this;
    }
}
