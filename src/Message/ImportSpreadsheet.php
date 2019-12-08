<?php

namespace App\Message;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class ImportSpreadsheetMessage
 * @package App\Message
 */
class ImportSpreadsheet
{
    /**
     * @var int
     */
    private $spreadsheetId;
    private $importData;

    public function __construct(int $spreadsheetId, $importData)
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->importData = $importData;
    }

    public function getSpreadsheetId(): int
    {
        return $this->spreadsheetId;
    }

    /**
     * @return mixed
     */
    public function getImportData()
    {
        return $this->importData;
    }
}