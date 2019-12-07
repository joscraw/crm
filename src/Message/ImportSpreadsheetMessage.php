<?php

namespace App\Message;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class ImportSpreadsheetMessage
 * @package App\Message
 */
class ImportSpreadsheetMessage
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}