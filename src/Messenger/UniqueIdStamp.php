<?php

namespace App\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

class UniqueIdStamp implements StampInterface
{
    private $uniqueId;

    public function __construct()
    {
        $this->uniqueId = uniqid();
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}