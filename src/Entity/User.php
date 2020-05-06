<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{

    use TimestampableEntity;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    private $firstName;

    /**
     * @Groups({"USERS_FOR_DATATABLE"})
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    private $lastName;

    /**
     *
     * @Groups({"USERS_FOR_DATATABLE"})
     *
     * @Assert\Count(min = 1, minMessage = "You must select at least one role!", groups={"CREATE", "EDIT"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="users", cascade={"persist"})
     */
    private $customRoles;

    /**
     * This is the auth0 user id
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sub;

    /**
     * @var string
     */
    private $token;

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AclSecurityIdentity", mappedBy="user")
     */
    private $aclSecurityIdentities;

    public function __construct()
    {
        $this->customRoles = new ArrayCollection();
        $this->aclSecurityIdentities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string|null The encoded password if any
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        // todo follow this https://auth0.com/blog/developing-modern-apps-with-symfony-and-react/
        // todo and implement equitable interface.

        return (string) $this->email;
    }

    /**
     * We use dynamic roles from the db so we proxy those from customRoles
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->customRoles->map(function(Role $role) {
            return $role->getName();
        });

        $roles = $roles->toArray();

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        $roles = array_merge($this->roles, $roles);

        return array_unique($roles);
    }

    public function addRole($role) {

        if(!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getCustomRoles(): Collection
    {
        return $this->customRoles;
    }

    public function addCustomRole(Role $customRole): self
    {
        if (!$this->customRoles->contains($customRole)) {
            $this->customRoles[] = $customRole;
        }

        return $this;
    }

    public function removeCustomRole(Role $customRole): self
    {
        if ($this->customRoles->contains($customRole)) {
            $this->customRoles->removeElement($customRole);
        }

        return $this;
    }

    public function getSub(): ?string
    {
        return $this->sub;
    }

    public function setSub(?string $sub): self
    {
        $this->sub = $sub;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return Collection|AclSecurityIdentity[]
     */
    public function getAclSecurityIdentities(): Collection
    {
        return $this->aclSecurityIdentities;
    }

    public function addAclSecurityIdentity(AclSecurityIdentity $aclSecurityIdentity): self
    {
        if (!$this->aclSecurityIdentities->contains($aclSecurityIdentity)) {
            $this->aclSecurityIdentities[] = $aclSecurityIdentity;
            $aclSecurityIdentity->setUser($this);
        }

        return $this;
    }

    public function removeAclSecurityIdentity(AclSecurityIdentity $aclSecurityIdentity): self
    {
        if ($this->aclSecurityIdentities->contains($aclSecurityIdentity)) {
            $this->aclSecurityIdentities->removeElement($aclSecurityIdentity);
            // set the owning side to null (unless already changed)
            if ($aclSecurityIdentity->getUser() === $this) {
                $aclSecurityIdentity->setUser(null);
            }
        }

        return $this;
    }

}
