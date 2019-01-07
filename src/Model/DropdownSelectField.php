<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class DropdownSelectField extends AbstractField implements \JsonSerializable
{
    /**
     * @var string
     */
    protected static $name = FieldCatalog::DROPDOWN_SELECT;

    /**
     * @var string
     */
    protected static $description = 'Dropdown Select Field';

    /**
     * Options for the dropdown select field
     *
     * @Assert\Valid
     *
     * @var FieldOption[]
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
                'description'   => $this->getDescription(),
                'options' => $this->getOptions(),
            ]
        );
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