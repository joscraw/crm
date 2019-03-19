<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use App\Validator\Constraints\PasswordsMustMatch;
use Symfony\Component\Serializer\Annotation\Groups;
use Rollerworks\Component\PasswordStrength\Validator\Constraints as RollerworksPassword;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @CustomAssert\PasswordsMustMatch(groups={"CREATE", "EDIT"})
 */
class User implements UserInterface
{

    use TimestampableEntity;

    const ROLE_ADMIN_USER = 'ROLE_ADMIN_USER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget an email for your user!", groups={"CREATE", "EDIT"})
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @RollerworksPassword\PasswordRequirements(requireLetters=true, requireNumbers=true, requireCaseDiff=true, requireSpecialCharacter= true, minLength = "6", groups={"CREATE", "EDIT"})
     * @Assert\NotBlank(message="Don't forget a password for your user!", groups={"CREATE"})
     *
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(message="Don't forget the password repeat field!", groups={"CREATE"})
     * @var string password repeat
     */
    private $passwordRepeat;

    /**
     * @Assert\NotBlank(message="Don't forget a first name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24)
     */
    private $firstName;

    /**
     * @Assert\NotBlank(message="Don't forget a last name for your user!", groups={"CREATE", "EDIT"})
     *
     * @ORM\Column(type="string", length=24)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $passwordResetToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordResetTokenTimestamp;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Portal", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $portal;

    /**
     *
     * @Assert\Count(min = 1, minMessage = "You must select at least one role!", groups={"CREATE", "EDIT"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="users")
     */
    private $customRoles;

    public function __construct()
    {
        $this->customRoles = new ArrayCollection();
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
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
     * @return string
     */
    public function getPasswordRepeat(): ?string
    {
        return $this->passwordRepeat;
    }

    /**
     * @param string $passwordRepeat
     */
    public function setPasswordRepeat(?string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): self
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    public function getPasswordResetTokenTimestamp(): ?\DateTimeInterface
    {
        return $this->passwordResetTokenTimestamp;
    }

    public function setPasswordResetTokenTimestamp(?\DateTimeInterface $passwordResetTokenTimestamp): self
    {
        $this->passwordResetTokenTimestamp = $passwordResetTokenTimestamp;

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

    /**
     * We check here for existence of a permission on a user
     *
     * @param $permission
     * @param $permissionType
     * @return bool
     */
    public function hasPermission($permission, $permissionType) {

        foreach($this->getCustomRoles() as $customRole) {

            switch ($permissionType) {

                case Role::OBJECT_PERMISSION:
                    $permissions = new ArrayCollection($customRole->getObjectPermissions());
                    break;

                case Role::SYSTEM_PERMISSION:
                    $permissions = new ArrayCollection($customRole->getSystemPermissions());
                    break;
            }

            $exists =  $permissions->exists(function($key, $element) use ($permission){
                return $element === $permission || $element === 'ALL';
            });

            if($exists) {
                return true;
            }
        }

        return false;
    }
}
