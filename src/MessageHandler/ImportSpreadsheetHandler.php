<?php

namespace App\MessageHandler;

use App\Entity\Record;
use App\Message\ImportSpreadsheet;
use App\Repository\CustomObjectRepository;
use App\Repository\SpreadsheetRepository;
use App\Service\PhpSpreadsheetHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Profiler\Profiler;
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
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * ImportSpreadsheetHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SpreadsheetRepository $spreadsheetRepository
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param string $uploadsPath
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SpreadsheetRepository $spreadsheetRepository,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        string $uploadsPath,
        CustomObjectRepository $customObjectRepository
    ) {
        $this->entityManager = $entityManager;
        $this->spreadsheetRepository = $spreadsheetRepository;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadsPath = $uploadsPath;
        $this->customObjectRepository = $customObjectRepository;

        // Help Prevent Memory leakage
        $this->entityManager->getConfiguration()->setSQLLogger(null);
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
        // Make sure garbage collection is enabled.
        gc_enable();

        $spreadsheetId = $message->getSpreadsheetId();
        $spreadsheet = $this->spreadsheetRepository->find($spreadsheetId);
        $customObjectId = $spreadsheet->getCustomObject()->getId();
        $mappings = $spreadsheet->getMappings();

        if(!$spreadsheet) {
             if ($this->logger) {
                 $this->logger->alert(sprintf('Spreadsheet %d was missing!', $spreadsheetId));
             }
            return;
        }

        if(empty($mappings)) {
            if ($this->logger) {
                $this->logger->alert(sprintf('Spreadsheet mapping missing for spreadsheet %d!', $spreadsheetId));
            }
            return;
        }

        $path = $this->uploadsPath.'/'.$spreadsheet->getPath();
        $file = new File($path);

        try {
            $reader = $this->phpSpreadsheetHelper->getReader($file);
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->alert(sprintf('Error loading spreadsheet reader for spreadsheet: %d (%s).', $spreadsheetId, $exception->getMessage()));
            }
            return;
        }

        try {
            $this->entityManager->beginTransaction();

            /** @var \Box\Spout\Reader\SheetInterface $sheet */
            foreach ($reader->getSheetIterator() as $sheet) {
                /** @var \Box\Spout\Common\Entity\Row $row */
                $columns = [];
                $batchSize = 20;
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $values = [];
                    $importData = [];

                    $cells = $row->getCells();
                    foreach ($cells as $cell) {
                        $value = $cell->getValue();
                        if($rowIndex === 1) {
                            $columns[] = $value;

                        } else {
                            $values[] = $value;
                        }
                    }

                    if($rowIndex > 1) {
                        $record = new Record();
                        $data = array_combine($columns, $values);
                        foreach($data as $column => $value) {
                            $mapping = array_filter($mappings, function($mapping) use($column) {
                                return !empty($mapping['mapped_from']) &&  $mapping['mapped_from'] === $column;
                            });
                            $mapping = array_values($mapping);
                            if(!empty($mapping[0]) && !empty($mapping[0]['mapped_to'])) {
                                $importData[$mapping[0]['mapped_to']] = $value;
                            }
                        }
                        $record->setProperties($importData);
                        // Because we are batching and completely clearing the entity manager
                        // to conserve memory, we need to re-fetch the custom object entity each time
                        $record->setCustomObject($this->customObjectRepository->find($customObjectId));
                        $this->entityManager->persist($record);
                    }

                    if (($rowIndex % $batchSize) === 0) {
                        $this->entityManager->flush();
                        echo sprintf("Records imported %s", $rowIndex);
                        $this->entityManager->clear();
                    }

                    // clean up unused variables to conserve memory
                    // I didn't really notice hardly any memory improvement
                    // with un setting these. But I know it def helps regardless
                    unset($mapping);
                    unset($column);
                    unset($record);
                    unset($data);
                    unset($cell);
                    unset($cells);
                    unset($values);
                    unset($rowIndex);
                    unset($importData);
                    unset($row);
                    unset($value);
                    gc_collect_cycles();
                }
            }
            // make sure any final records that came after the (($rowIndex % $batchSize) === 0) are flushed
            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            if ($this->logger) {
                $this->logger->alert(sprintf('Error parsing spreadsheet rows for spreadsheet: %d (%s).', $spreadsheetId, $exception->getMessage()));
            }
            return;
        }

    }
}