<?php

namespace App\MessageHandler;

use App\Entity\Record;
use App\Message\ImportSpreadsheet;
use App\Repository\SpreadsheetRepository;
use App\Service\PhpSpreadsheetHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class ImportSpreadsheetHandler
 * @package App\MessageHandler
 */
class ImportSpreadsheetHandler implements MessageHandlerInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SpreadsheetRepository
     */
    private $spreadsheetRepository;
    /**
     * @var PhpSpreadsheetHelper
     */
    private $phpSpreadsheetHelper;

    /**
     * @var string
     */
    private $uploadsPath;

    /**
     * ImportSpreadsheetHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SpreadsheetRepository $spreadsheetRepository
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param $uploadsPath
     */
    public function __construct(EntityManagerInterface $entityManager,
        SpreadsheetRepository $spreadsheetRepository,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        $uploadsPath
    )
    {
        $this->entityManager = $entityManager;
        $this->spreadsheetRepository = $spreadsheetRepository;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadsPath = $uploadsPath;
    }

    /**
     * @see https://symfonycasts.com/screencast/messenger
     * NOTES:
     * 1. Make sure every time you make a change to a handler you stop and restart the workers
     *
     * 2. To start the workers run ./bin/console messenger:consume -vv (for verbosity)
     *
     * 3. When needing to pass an entity to a handler just pass the ID of the entity
     * into the Message and query for it inside the handler. If you were to pass the whole entity
     * in and try to make a change to to it and call $entityManager->Flush()
     * Doctrine would not be managing it in it's IdentityMap and no changes would happen. If you called
     * $entityManager->persist() then $entityManager->Flush() then doctrine would end up creating a brand new entity!
     * This is not desired behavior as you just want to make changes to the entity that you passed in. The solution is simple!
     * Just pass the ID of the entity into the message object and then query for that entity Example:
     * $user = $this->userRepository->find($message->getUserId())
     *
     * 4. If the entity gets deleted from the db before the handler picks it up this can throw an error and halt your worker.
     * Make sure you check to make sure the entity exists after querying for it. Example: if($user) {//then perform your actions here}
     * If the entity is not found you have 2 options on what you can do. Option 1. Just return. if(!$user) {return;}. If you do this
     * then the message will be removed from the queue and will not retry. Option 2. You can throw an exception and then the message will
     * get retried later.
     *
     * 5. An aknowledged message means the message was handeled and removed from the queue. https://cl.ly/a796c7daa7e1 Even if you
     * return from the __invoke for whatever reason, it will remove the message from the queue and say it was aknowledged.
     *
     * @param ImportSpreadsheet $message
     */
    public function __invoke(ImportSpreadsheet $message)
    {
        // NOTE 3
        $spreadsheetId = $message->getSpreadsheetId();
        $spreadsheet = $this->spreadsheetRepository->find($spreadsheetId);
        // NOTE 4
        if(!$spreadsheet) {
            if ($this->logger) {
                $this->logger->alert(sprintf('Spreadsheet %d was missing!', $spreadsheetId));
            }
            return;
        }
        $path = $this->uploadsPath.'/'.$spreadsheet->getPath();
        $file = new File($path);
        // if the service can't load the rows just return
        if(!$rows = $this->phpSpreadsheetHelper->getAllRows($file)) {
            return;
        }
        $importData = $message->getImportData();
        // The first row is the columns. So let's go ahead and remove those
        $columns = array_shift($rows);
        foreach($rows as $row) {
            $record = new Record();
            $properties = [];
            foreach($row as $index => $column) {
                $formFriendlyName = $this->phpSpreadsheetHelper->formFriendly($columns[$index])[0];
                $internalName = $importData[$formFriendlyName . '_properties'];
                // if the user chose unmapped for one of the columns
                // go ahead and use the form friendly column name from the csv
                if($internalName === 'unmapped') {
                    $properties[$formFriendlyName] = $column;
                } else {
                    $properties[$internalName] = $column;
                }
            }
            $record->setProperties($properties);
            $record->setCustomObject($spreadsheet->getCustomObject());
            $this->entityManager->persist($record);
        }
        $this->entityManager->flush();
        echo sprintf("Import successfully handled");
    }
}