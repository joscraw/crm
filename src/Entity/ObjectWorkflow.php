<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ObjectWorkflowRepository")
 */
class ObjectWorkflow extends Workflow
{
    /**
     * @Groups({"WORKFLOW"})
     * @var string
     */
    public static $nameDisc = 'objectWorkflow';

    /**
     * @Groups({"WORKFLOW"})
     * @ORM\ManyToOne(targetEntity="App\Entity\CustomObject", inversedBy="objectWorkflows")
     */
    private $customObject;

    public function getCustomObject(): ?CustomObject
    {
        return $this->customObject;
    }

    public function setCustomObject(?CustomObject $customObject): self
    {
        $this->customObject = $customObject;

        return $this;
    }

    /**
     * @return string
     */
    public static function getNameDisc()
    {
        return self::$nameDisc;
    }
}
