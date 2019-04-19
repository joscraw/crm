<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractChoiceField
 * @package App\Model
 */
class AbstractChoiceField extends AbstractField
{
    /**
     * Options for the dropdown select field
     *
     * @Groups({"PROPERTY_FIELD_NORMALIZER"})
     *
     * @Assert\Valid
     *
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one option",
     *      groups={"CREATE", "EDIT"}
     * )
     *
     * @var FieldOption[]
     */
    protected $options;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }
    /**
     * @return array
     */
    public function getOptionsForChoiceTypeField() {
        $choices = [];
        foreach($this->options as $option) {
            $choices[$option->getLabel()] = $option->getLabel();
        }
        return $choices;
    }

    /**
     * @return FieldOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param FieldOption[] $options
     */
    public function setOptions($options): void
    {
        $this->options = $options;
    }
}