<?php

namespace App\Command;

use App\Entity\Record;
use App\Mailer\ResetPasswordMailer;
use App\Repository\CustomObjectRepository;
use App\Repository\RecordRepository;
use App\Repository\SpreadsheetRepository;
use App\Repository\UserRepository;
use App\Service\PhpSpreadsheetHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class TestCommand extends Command
{
    /*use LoggerAwareTrait;*/

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:test';

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * @var UserRepository
     */
    private $userRepository;

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
     * TestCommand constructor.
     * @param RecordRepository $recordRepository
     * @param ResetPasswordMailer $resetPasswordMailer
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param SpreadsheetRepository $spreadsheetRepository
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param string $uploadsPath
     * @param CustomObjectRepository $customObjectRepository
     */
    public function
    __construct(
        RecordRepository $recordRepository,
        ResetPasswordMailer $resetPasswordMailer,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        SpreadsheetRepository $spreadsheetRepository,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        string $uploadsPath,
        CustomObjectRepository $customObjectRepository
    ) {
        $this->recordRepository = $recordRepository;
        $this->resetPasswordMailer = $resetPasswordMailer;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->spreadsheetRepository = $spreadsheetRepository;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadsPath = $uploadsPath;
        $this->customObjectRepository = $customObjectRepository;

        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // Help Prevent Memory leakage
        $this->entityManager->getConfiguration()->setSecondLevelCacheEnabled(null);
        $this->entityManager->getConfiguration()->setSQLLogger(null);

        $probe = \BlackfireProbe::getMainInstance();
        $probe->enable();

        $this->test($input, $output);

        $probe->disable();
    }

    protected function test($input, $output)
    {

        // Make sure garbage collection is enabled.
        gc_enable();

        $spreadsheetId = 31;
        $spreadsheet = $this->spreadsheetRepository->find($spreadsheetId);
        $customObjectId = $spreadsheet->getCustomObject()->getId();
        $mappings = $spreadsheet->getMappings();
        // NOTE 4
        if(!$spreadsheet) {
           /* if ($this->logger) {
                $this->logger->alert(sprintf('Spreadsheet %d was missing!', $spreadsheetId));
            }*/
            return;
        }

        if(empty($mappings)) {
            /*if ($this->logger) {
                $this->logger->alert(sprintf('Spreadsheet mapping missing for spreadsheet %d!', $spreadsheetId));
            }*/
            return;
        }

        $path = $this->uploadsPath.'/'.$spreadsheet->getPath();
        $file = new File($path);
        // if the service can't load the rows just return
        if(!$worksheet = $this->phpSpreadsheetHelper->getWorksheet($file)) {
            return;
        }

        $columns = [];
        $batchSize = 20;
        foreach ($worksheet->getRowIterator() as $row) {
            $values = [];
            $importData = [];
            $cellIterator = $row->getCellIterator();

            /**
             * This loops through all cells, even if a cell value is not set.
             * By default, only cells that have a value set will be iterated.
             */
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                if($row->getRowIndex() === 1) {
                    $columns[] = $value;

                } else {
                    $values[] = $value;
                }
            }

            if($row->getRowIndex() > 1) {
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
                $record->setCustomObject($this->customObjectRepository->find($customObjectId));
                $this->entityManager->persist($record);
            }

            if (($row->getRowIndex() % $batchSize) === 0) {
                $this->entityManager->flush();
                echo sprintf("Records imported %s", $row->getRowIndex());
                $this->entityManager->clear();
            }

            // clean up unused variables to conserve memory
            unset($mapping);
            unset($column);
            unset($record);
            unset($data);
            unset($cell);
            unset($values);
            unset($cellIterator);
            unset($importData);
            unset($row);
            unset($value);
            gc_collect_cycles();

        }

        $this->entityManager->flush();

        echo sprintf("Import successfully handled");

        $output->writeln([
            'done',
            '============',
            '',
        ]);

    }
}