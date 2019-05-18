<?php

namespace App\Model;

/**
 * Trait FormFieldProperties
 * @package App\Model
 */
trait FormFieldProperties
{
    /**
     * @var string
     */
    protected $helpText;

    /**
     * @var string
     */
    protected $placeholderText;

    /**
     * @var string
     */
    protected $uid;

    /**
     * @return string
     */
    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    /**
     * @param string $helpText
     */
    public function setHelpText(string $helpText): void
    {
        $this->helpText = $helpText;
    }

    /**
     * @return string
     */
    public function getPlaceholderText(): ?string
    {
        return $this->placeholderText;
    }

    /**
     * @param string $placeholderText
     */
    public function setPlaceholderText(string $placeholderText): void
    {
        $this->placeholderText = $placeholderText;
    }

    /**
     * @return string
     */
    public function getUid(): ?string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }
}