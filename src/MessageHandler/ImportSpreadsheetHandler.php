<?php

namespace App\MessageHandler;

use App\Entity\Property;
use App\Entity\Record;
use App\Entity\RecordDuplicate;
use App\Message\ImportSpreadsheet;
use App\Model\Filter\Column;
use App\Model\Filter\FilterData;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use App\Repository\SpreadsheetRepository;
use App\Service\PhpSpreadsheetHelper;
use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @see https://symfony.com/doc/4.2/messenger.html
 * Class ImportSpreadsheetHandler
 * @package App\MessageHandler
 */
class ImportSpreadsheetHandler implements MessageHandlerInterface, LoggerAwareInterface
{

    use LoggerAwareTrait;
    use RandomStringGenerator;

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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var AdapterInterface $cache
     */
    private $cache;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * ImportSpreadsheetHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SpreadsheetRepository $spreadsheetRepository
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param string $uploadsPath
     * @param CustomObjectRepository $customObjectRepository
     * @param ValidatorInterface $validator
     * @param AdapterInterface $cache
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SpreadsheetRepository $spreadsheetRepository,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        string $uploadsPath,
        CustomObjectRepository $customObjectRepository,
        ValidatorInterface $validator,
        AdapterInterface $cache,
        PropertyRepository $propertyRepository
    ) {
        $this->entityManager = $entityManager;
        $this->spreadsheetRepository = $spreadsheetRepository;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadsPath = $uploadsPath;
        $this->customObjectRepository = $customObjectRepository;
        $this->validator = $validator;
        $this->cache = $cache;
        $this->propertyRepository = $propertyRepository;

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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __invoke(ImportSpreadsheet $message)
    {
        // Make sure garbage collection is enabled.
        gc_enable();
        $duplicateRecords = [];

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

        $uniqueProperties = $this->propertyRepository->findBy([
           'customObject' => $spreadsheet->getCustomObject(),
           'isUnique' => true,
        ]);

        $filterData = new FilterData();
        $filterData->setBaseObject($spreadsheet->getCustomObject());
        /** @var Property $uniqueProperty */
        foreach($uniqueProperties as $uniqueProperty) {
            $column = new Column();
            $column->setRenameTo($uniqueProperty->getInternalName());
            $column->setProperty($uniqueProperty);
            $filterData->addColumn($column);
        }
        $results = $filterData->runQuery($this->entityManager);

/*        $existingEmails = [];
        if(!empty($results['results'])) {
            $existingEmails = array_column($results['results'], 'email');
        }

        $emailProperty = $this->entityManager->getRepository(Property::class)->findOneBy([
            'customObject' => $spreadsheet->getCustomObject(),
            'internalName' => 'email',
        ]);*/

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

                        // Normalizing empty cell possibilities https://github.com/box/spout/issues/332
                        if(count($columns) !== count($values)) {
                            $values = $values + array_fill(count($values), count($columns) - count($values), '');
                        }
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

                  /*      $conflictingRecords = array_filter($results['results'], function($result) use($uniqueProperties, $record) {
                            foreach($uniqueProperties as $uniqueProperty) {
                                $internalName = $uniqueProperty->getInternalName();
                                // we probably need to check for the existence of $internalName in $result here.
                                // This is extremely slow. Ughh!!!! I hate this.
                                // I Don't know if there is any way around it when you are trying to dedup data on an import.
                                // if the spreadsheet is super large this $result['results'] array is going to keep growing
                                // which will slow down the import as it keeps going which will keep eating up more and more cpu.
                                if($record->$internalName === $result[$internalName]) {
                                    return true;
                                }
                            }
                            return false;
                        });*/

                        if (($rowIndex % $batchSize) === 0) {
                            echo sprintf("Records skipped %s", $rowIndex);
                        }

                        foreach($uniqueProperties as $uniqueProperty) {
                            $internalName = $uniqueProperty->getInternalName();
                            $data = array_column($results['results'], $internalName);
                            if(in_array($record->$internalName, $data)) {
                                // todo possibly collect the duplicate records here along with which columns were duplicates
                                continue 2;
                            }
                        }
                        
                        //$existingEmails = array_column($results['results'], 'email');


                        // duplicate records were found with the current record trying to be imported
                        // For now just add the record to the duplicateRecords array and we will deal
                        // with these later
                    /*    if(!empty($conflictingRecords)) {
                            $duplicateRecords[] = $record;
                            continue;
                        }*/

                        // add the record onto the results stack used for the deduplicate logic
                        // to prevent multiple rows from the spreadsheet with the same unique
                        // properties from being imported
                        $results['results'][] = $record->getProperties();

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
                    unset($conflictingRecords);
                    gc_collect_cycles();
                }
            }
            // make sure any final records that came after the (($rowIndex % $batchSize) === 0) are flushed
            $this->entityManager->flush();
            $this->entityManager->commit();

            // Handle duplicate records
            $customObject = $this->customObjectRepository->find($customObjectId);
            /** @var Record $record */
            /*foreach($duplicateRecords as $record) {*/
               /* $recordDuplicate = new RecordDuplicate();
                $recordDuplicate->setCustomObject($customObject);
                $recordDuplicate->setProperties($record->getProperties());*/

            /*}*/

            echo "Import successfully completed...";

        } catch (\Exception $exception) {
            $this->entityManager->rollback();
            if ($this->logger) {
                $this->logger->alert(sprintf('Error parsing spreadsheet rows for spreadsheet: %d (%s).', $spreadsheetId, $exception->getMessage()));
            }
            return;
        }

    }
}