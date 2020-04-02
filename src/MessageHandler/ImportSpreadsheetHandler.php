<?php

namespace App\MessageHandler;

use App\Entity\Property;
use App\Entity\Record;
use App\Message\ImportSpreadsheet;
use App\Model\Filter\Column;
use App\Model\Filter\FilterData;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use App\Repository\SpreadsheetRepository;
use App\Service\PhpSpreadsheetHelper;
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


        $emailProperty = $this->entityManager->getRepository(Property::class)->findOneBy([
            'customObject' => $spreadsheet->getCustomObject(),
            'internalName' => 'email'
        ]);

        $existingEmails = [];
        if($emailProperty) {
            $column = new Column();
            $column->setProperty($emailProperty);
            $column->setRenameTo('email');
            $filterData = new FilterData();
            $filterData->setBaseObject($spreadsheet->getCustomObject());
            $filterData->addColumn($column);
            $results = $filterData->runQuery($this->entityManager);
            if(!empty($results['results'])) {
                $existingEmails = array_column($results['results'], 'email');
            }
            echo "email results captured... \n";
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

                        $email = $record->email;

                        // todo how do we handle imports having the same email address inside of each spreadsheet. We
                        //  don't want those duplicates getting added either right?
                        if(!empty($email) && in_array($email, $existingEmails)) {
                            echo "email already exists in system. Skipping import.... $rowIndex \n ";
                            continue;
                        } else {
                            $existingEmails[] = $email;
                        }

                        /*$errors = $this->validator->validate($record);*/

                        // Let's not import the record if there are any validation errors
                        // todo we can't run a query each time we need to validate on whether or not an email exists. We need
                        //  some type of caching file or something to query the emails from.
                       /* $errors = $this->validator->validate($record);
                        if (count($errors) > 0) {*/
                            // if we have any errors let's go ahead and flush any records we have being managed
                            // and clear the entity manager. The reason we need to clear here is that the validation
                            // above actually runs queries and we need to make sure the MYSQL Memory is staying low
                            /*$this->entityManager->flush();
                            $this->entityManager->clear();*/
                         /*   echo $rowIndex . "  ";
                            continue;
                        }*/

         /*               if($this->cache->hasItem('contact_emails')) {
                            $item = $this->cache->getItem('contact_emails');
                            $contactEmails = $item->get();
                            if(in_array($record->email, $contactEmails)) {
                                echo "Record already exists.   ";
                                continue;
                            }
                        }*/

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