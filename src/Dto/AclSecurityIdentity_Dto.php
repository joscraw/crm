<?php

namespace App\Dto;

use App\Dto\DataTransformer\AclSecurityIdentity_DtoTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Http\Api;
use App\Annotation\ApiVersion;
use App\Annotation\Identifier;
use Swagger\Annotations as SWG;

/**
 * Class AclEntry_Dto
 * @package App\Dto
 *
 * @ApiVersion({Api::VERSION_1})
 * @Identifier({DtoFactory::ACL_SECURITY_IDENTITY})
 *
 */
class AclSecurityIdentity_Dto extends Dto
{
    /**
     *
     * @SWG\Property(property="securityIdentity", type="string", example="App\Entity\User-23")
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\NotBlank(message="Don't forget a security identity.", groups={Dto::GROUP_CREATE, Dto::GROUP_UPDATE})
     *
     * @var string
     */
    private $securityIdentity;

    /**
     * @return string
     */
    public function getSecurityIdentity(): string
    {
        return $this->securityIdentity;
    }

    /**
     * @SWG\Property(property="aclEntries", type="array", @Model(type=AclEntry_Dto::class))
     *
     * @Groups({Dto::GROUP_CREATE, Dto::GROUP_UPDATE, Dto::GROUP_DEFAULT})
     *
     * @Assert\Valid
     *
     * @var AclEntry_Dto[]
     */
    private $aclEntries;

    public function __construct()
    {
        $this->aclEntries = new ArrayCollection();
    }


    /**
     * @param string $securityIdentity
     * @return AclSecurityIdentity_Dto
     */
    public function setSecurityIdentity(string $securityIdentity): self
    {
        $this->securityIdentity = $securityIdentity;

        return $this;
    }

    /**
     * @return Collection|AclEntry_Dto[]
     */
    public function getAclEntries(): Collection
    {
        return $this->aclEntries;
    }

    /**
     * @param AclEntry_Dto $aclEntry
     * @return AclSecurityIdentity_Dto
     */
    public function addAclEntry(AclEntry_Dto $aclEntry): self
    {
        if (!$this->aclEntries->contains($aclEntry)) {
            $this->aclEntries[] = $aclEntry;
        }

        return $this;
    }

    /**
     * @param AclEntry_Dto $aclEntry
     * @return AclSecurityIdentity_Dto
     */
    public function removeAclEntry(AclEntry_Dto $aclEntry): self
    {
        if ($this->aclEntries->contains($aclEntry)) {
            $this->aclEntries->removeElement($aclEntry);
        }

        return $this;
    }

    public function getDataTransformer()
    {
        return AclSecurityIdentity_DtoTransformer::class;
    }
}