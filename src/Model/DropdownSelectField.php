<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class DropdownSelectField extends AbstractField implements \JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = 'dropdown_select_field';

    /**
     * @var string
     */
    protected static $description = 'Dropdown Select Field';

    /**
     * Is this a multi-select?
     *
     * @var boolean
     */
    private $isMultiSelect = false;

    /**
     * Options for the multiple-choice interaction.
     *
     * @Assert\Valid
     *
     * @var DropdownSelectFieldOption[]
     */
    private $options;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'name' => $this->getName(),
                'options' => $this->getOptions(),
                'isMultiSelect' => $this->isMultiSelect(),
            ]
        );
    }

    /**
     * @return DropdownSelectFieldOption[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param DropdownSelectFieldOption[] $options
     */
    public function setOptions($options): void
    {
        $this->options = $options;
    }

    /**
     * @return bool
     */
    public function isMultiSelect(): bool
    {
        return $this->isMultiSelect;
    }

    /**
     * @param bool $isMultiSelect
     * @return DropdownSelectField
     */
    public function setIsMultiSelect(bool $isMultiSelect): self
    {
        $this->isMultiSelect = $isMultiSelect;

        return $this;
    }
}