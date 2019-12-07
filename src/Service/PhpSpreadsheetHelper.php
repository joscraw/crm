<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhpSpreadsheetHelper
{
    /**
     * Return the column names from the CSV
     * @param UploadedFile $uploadedFile
     * @return array|bool
     */
    public function getColumnNames(UploadedFile $uploadedFile) {
        $tempPathName = $uploadedFile->getRealPath();
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $error = false;
        $reader = false;
        $spreadsheet = false;
        $columns = false;
        switch ($fileExtension) {
            case 'xlsx':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                break;
            case 'xls':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                break;
            case 'csv':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                break;
        }
        if($reader) {
            try {
                $chunkFilter = new ChunkReadFilter();
                $chunkFilter->setRows(1,1);
                $reader->setReadFilter($chunkFilter);
                $spreadsheet = $reader->load($tempPathName);
            } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
                $error = 'Error reading in file: ' . $exception->getMessage();
            }
        }
        if($spreadsheet && !$error) {
            try {
                $columns = $spreadsheet->getActiveSheet()->toArray();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
                $error = 'Error converting spreadsheet to an array: ' . $exception->getMessage();
            }
        }
        if(!$error && !empty($columns[0])) {
            // remove empty or null values from array
            return array_filter($columns[0]);
        }
        return false;
    }

    /**
     * Return all the spreadsheet rows
     * @param UploadedFile $uploadedFile
     * @return array|bool
     */
    public function getAllRows(UploadedFile $uploadedFile) {
        $tempPathName = $uploadedFile->getRealPath();
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $error = false;
        $reader = false;
        $spreadsheet = false;
        $rows = false;
        switch ($fileExtension) {
            case 'xlsx':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                break;
            case 'xls':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                break;
            case 'csv':
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                break;
        }
        if($reader) {
            try {
                $spreadsheet = $reader->load($tempPathName);
            } catch(\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
                $error = 'Error reading in file: ' . $exception->getMessage();
            }
        }
        if($spreadsheet && !$error) {
            try {
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
                $error = 'Error converting spreadsheet to an array: ' . $exception->getMessage();
            }
        }
        if(!$error && !empty($rows)) {
            // remove empty or null values from array
            return $rows;
        }
        return false;
    }

    /**
     * @param $columns
     * @return string|string[]|null
     */
    public function formFriendly($columns) {
        $columns = !is_array($columns) ? [$columns] : $columns;
        $columns = preg_replace('/[^a-zA-Z0-9_ ]/', '', $columns);
        $columns = array_map('strtolower', $columns);
        $columns = array_map(
            function($str) {
                return str_replace(' ', '_', $str);
            },
            $columns
        );
        return $columns;
    }

    /**
     * @param $columns
     * @return string|string[]|null
     */
    public function choicesForForm($columns) {
        $columns = !is_array($columns) ? [$columns] : $columns;
        $choices = [];
        foreach($columns as $column) {
            $choices[$this->formFriendly($column)[0]] = $column;
        }
        return $choices;
    }
}