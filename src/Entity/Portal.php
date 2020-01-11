<?php

namespace App\Entity;

use App\Utils\RandomStringGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PortalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Portal
{
    use RandomStringGenerator;

    /**
     * @Groups({"FORMS"})
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"FORMS"})
     * @ORM\Column(type="string", length=255)
     */
    private $internalIdentifier;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CustomObject", mappedBy="portal", cascade={"remove"})
     */
    private $customObjects;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Report", mappedBy="portal", orphanRemoval=true)
     */
    private $reports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="portal", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Role", mappedBy="portal", orphanRemoval=true)
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Filter", mappedBy="portal", orphanRemoval=true)
     */
    private $filters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MarketingList", mappedBy="portal", orphanRemoval=true)
     */
    private $marketingLists;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Folder", mappedBy="portal", orphanRemoval=true)
     */
    private $folders;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Form", mappedBy="portal")
     */
    private $forms;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Workflow", mappedBy="portal", orphanRemoval=true)
     */
    private $workflows;

    /**
     * @ORM\OneToOne(targetEntity="GmailAccount", mappedBy="portal", cascade={"persist", "remove"})
     */
    private $gmailAccount;

    /**
     * @ORM\PrePersist
     */
    public function setInternalIdentifierValue()
    {
        if(!$this->internalIdentifier) {
            $this->internalIdentifier = $this->generateRandomNumber(10);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function __construct()
    {
        $this->customObjects = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->filters = new ArrayCollection();
        $this->marketingLists = new ArrayCollection();
        $this->folders = new ArrayCollection();
        $this->forms = new ArrayCollection();
        $this->workflows = new ArrayCollection();
    }

    /**
     * @return Collection|CustomObject[]
     */
    public function getCustomObjects(): Collection
    {
        return $this->customObjects;
    }

    public function addCustomObject(CustomObject $customObject): self
    {
        if (!$this->customObjects->contains($customObject)) {
            $this->customObjects[] = $customObject;
            $customObject->setPortal($this);
        }

        return $this;
    }

    public function removeCustomObject(CustomObject $customObject): self
    {
        if ($this->customObjects->contains($customObject)) {
            $this->customObjects->removeElement($customObject);
            // set the owning side to null (unless already changed)
            if ($customObject->getPortal() === $this) {
                $customObject->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInternalIdentifier(): ?string
    {
        return $this->internalIdentifier;
    }

    /**
     * @param $internalIdentifier
     * @return $this
     */
    public function setInternalIdentifier(string $internalIdentifier): self
    {
        $this->internalIdentifier = $internalIdentifier;

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setPortal($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getPortal() === $this) {
                $report->setPortal(null);
            }
        }

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
            $user->setPortal($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getPortal() === $this) {
                $user->setPortal(null);
            }
        }

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
            $role->setPortal($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            // set the owning side to null (unless already changed)
            if ($role->getPortal() === $this) {
                $role->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Filter[]
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    public function addFilter(Filter $filter): self
    {
        if (!$this->filters->contains($filter)) {
            $this->filters[] = $filter;
            $filter->setPortal($this);
        }

        return $this;
    }

    public function removeFilter(Filter $filter): self
    {
        if ($this->filters->contains($filter)) {
            $this->filters->removeElement($filter);
            // set the owning side to null (unless already changed)
            if ($filter->getPortal() === $this) {
                $filter->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MarketingList[]
     */
    public function getMarketingLists(): Collection
    {
        return $this->marketingLists;
    }

    public function addMarketingList(MarketingList $marketingList): self
    {
        if (!$this->marketingLists->contains($marketingList)) {
            $this->marketingLists[] = $marketingList;
            $marketingList->setPortal($this);
        }

        return $this;
    }

    public function removeMarketingList(MarketingList $marketingList): self
    {
        if ($this->marketingLists->contains($marketingList)) {
            $this->marketingLists->removeElement($marketingList);
            // set the owning side to null (unless already changed)
            if ($marketingList->getPortal() === $this) {
                $marketingList->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Folder[]
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): self
    {
        if (!$this->folders->contains($folder)) {
            $this->folders[] = $folder;
            $folder->setPortal($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): self
    {
        if ($this->folders->contains($folder)) {
            $this->folders->removeElement($folder);
            // set the owning side to null (unless already changed)
            if ($folder->getPortal() === $this) {
                $folder->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Form[]
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function addForm(Form $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms[] = $form;
            $form->setPortal($this);
        }

        return $this;
    }

    public function removeForm(Form $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
            // set the owning side to null (unless already changed)
            if ($form->getPortal() === $this) {
                $form->setPortal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Workflow[]
     */
    public function getWorkflows(): Collection
    {
        return $this->workflows;
    }

    public function addWorkflow(Workflow $workflow): self
    {
        if (!$this->workflows->contains($workflow)) {
            $this->workflows[] = $workflow;
            $workflow->setPortal($this);
        }

        return $this;
    }

    public function removeWorkflow(Workflow $workflow): self
    {
        if ($this->workflows->contains($workflow)) {
            $this->workflows->removeElement($workflow);
            // set the owning side to null (unless already changed)
            if ($workflow->getPortal() === $this) {
                $workflow->setPortal(null);
            }
        }

        return $this;
    }

    public function getGmailAccount(): ?GmailAccount
    {
        return $this->gmailAccount;
    }

    public function setGmailAccount(GmailAccount $gmailAccount): self
    {
        $this->gmailAccount = $gmailAccount;

        // set the owning side of the relation if necessary
        if ($gmailAccount->getPortal() !== $this) {
            $gmailAccount->setPortal($this);
        }

        return $this;
    }

}
