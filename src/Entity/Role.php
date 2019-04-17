<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Validator\Constraints as CustomAssert;


/**
 * @CustomAssert\RoleNameAlreadyExists()
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 */
class Role
{
    use TimestampableEntity;

    public static $permissions = [
        'ALL' => 'ALL',
        'REPORTS' => [
            'CREATE_REPORT' => 'CREATE_REPORT',
            'EDIT_REPORT' => 'EDIT_REPORT',
            'DELETE_REPORT' => 'DELETE_REPORT',
        ],
        'LISTS' => [
            'CREATE_LIST' => 'CREATE_LIST',
            'EDIT_LIST' => 'EDIT_LIST',
            'DELETE_LIST' => 'DELETE_LIST',
        ],
        'USERS' => [
            'CREATE_USER' => 'CREATE_USER',
            'EDIT_USER' => 'EDIT_USER',
            'DELETE_USER' => 'DELETE_USER',
        ],
        'ROLES' => [
            'CREATE_ROLE' => 'CREATE_ROLE',
            'EDIT_ROLE' => 'EDIT_ROLE',
            'DELETE_ROLE' => 'DELETE_ROLE',
        ],
        'CUSTOM OBJECTS' => [
            'CREATE_CUSTOM_OBJECT' => 'CREATE_CUSTOM_OBJECT',
            'EDIT_CUSTOM_OBJECT' => 'EDIT_CUSTOM_OBJECT',
            'DELETE_CUSTOM_OBJECT' => 'DELETE_CUSTOM_OBJECT',
        ],
        'PROPERTIES' => [
            'CREATE_PROPERTY' => 'CREATE_PROPERTY',
            'EDIT_PROPERTY' => 'EDIT_PROPERTY',
            'DELETE_PROPERTY' => 'DELETE_PROPERTY',
        ],
        'PROPERTY GROUPS' => [
            'CREATE_PROPERTY_GROUP' => 'CREATE_PROPERTY_GROUP',
            'EDIT_PROPERTY_GROUP' => 'EDIT_PROPERTY_GROUP',
            'DELETE_PROPERTY_GROUP' => 'DELETE_PROPERTY_GROUP',
        ]
    ];

    const OBJECT_PERMISSION = 'OBJECT_PERMISSION';
    const SYSTEM_PERMISSION = 'SYSTEM_PERMISSION';

    const CREATE_REPORT = 'CREATE_REPORT';
    const DELETE_REPORT = 'DELETE_REPORT';
    const EDIT_REPORT = 'EDIT_REPORT';

    const CREATE_LIST = 'CREATE_LIST';
    const DELETE_LIST = 'DELETE_LIST';
    const EDIT_LIST = 'EDIT_LIST';

    const CREATE_USER = 'CREATE_USER';
    const DELETE_USER = 'DELETE_USER';
    const EDIT_USER = 'EDIT_USER';

    const CREATE_ROLE = 'CREATE_ROLE';
    const DELETE_ROLE = 'DELETE_ROLE';
    const EDIT_ROLE = 'EDIT_ROLE';

    const CREATE_CUSTOM_OBJECT = 'CREATE_CUSTOM_OBJECT';
    const DELETE_CUSTOM_OBJECT = 'DELETE_CUSTOM_OBJECT';
    const EDIT_CUSTOM_OBJECT = 'EDIT_CUSTOM_OBJECT';

    const CREATE_PROPERTY = 'CREATE_PROPERTY';
    const DELETE_PROPERTY = 'DELETE_PROPERTY';
    const EDIT_PROPERTY = 'EDIT_PROPERTY';

    const CREATE_PROPERTY_GROUP = 'CREATE_PROPERTY_GROUP';
    const DELETE_PROPERTY_GROUP = 'DELETE_PROPERTY_GROUP';
    const EDIT_PROPERTY_GROUP = 'EDIT_PROPERTY_GROUP';


    /**
     * @Groups({"ROLES_FOR_DATATABLE"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"ROLES_FOR_DATATABLE", "USERS_FOR_DATATABLE", "ROLES_FOR_USER_FILTER"})
     * @Assert\NotBlank(message="Don't forget a name for your brand new Role!")
     * @Assert\Regex("/^[a-zA-Z0-9_]*$/", message="Woah! Only use letters numbers and underscores please!")
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
