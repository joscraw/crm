<?php

namespace App\Message;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class ImportSpreadsheetMessage
 * @package App\Message
 */
class ImportSpreadsheetMessage
{
    /**
     * @var int
     */
    private $spreadsheetId;

    public function __construct(int $spreadsheetId)
    {
        $this->spreadsheetId = $spreadsheetId;
    }

    public function getSpreadsheetId(): int
    {
        return $this->spreadsheetId;
    }
}