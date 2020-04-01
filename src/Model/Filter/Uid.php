<?php

namespace App\Model\Filter;

trait Uid
{
    /**
     * @var string
     */
    protected $uid;

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
    public function setUid(?string $uid): void
    {
        $this->uid = $uid;
    }
}