<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class FieldOption
{

    /**
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     *
     * @Assert\NotBlank(message="Don't forget to set a value for each one of your options!", groups={"CREATE", "EDIT"})
     *
     * @var string
     */
    private $label;

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return FieldOption
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

}