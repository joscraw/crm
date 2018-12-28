<?php

namespace App\Entity;

use App\Model\Content;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CustomObjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomObject
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Don't forget a label for your brand new sweeeeet Custom Object!")
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $label;

    /**
     * internal name
     *
     * @Assert\Regex("/^[a-zA-Z_]*$/", message="Woah! Only use letters and underscores please!")
     *
     * @ORM\Column(name="internal_name", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $internalName;

    /**
     * Custom Object Content
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @var Content
     */
    private $content;

    /**
     * @ORM\PrePersist
     */
    public function setInternalNameValue()
    {
        if(!$this->internalName) {
            $this->internalName = strtolower(
                preg_replace('/\s+/', '_', $this->getLabel())
            );
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getInternalName(): ?string
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     */
    public function setInternalName(string $internalName)
    {
        $this->internalName = $internalName;
    }

    /**
     * @return Content
     */
    public function getContent(): ?Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     */
    public function setContent(Content $content)
    {
        $this->content = $content;
    }
}
