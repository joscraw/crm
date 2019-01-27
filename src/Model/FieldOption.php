<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;


class FieldOption implements \JsonSerializable
{

    /**
     * @Assert\NotBlank(message="Don't forget to set a value for each one of your options!", groups={"CREATE", "EDIT"})
     *
     * @var string
     */
    private $label;

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return [
            'label'   => $this->getLabel()
        ];
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
     * @return FieldOption
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

}