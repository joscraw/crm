<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RecordImportSpreadsheet extends Constraint
{
    /**
     * RecordImportSpreadsheet constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
    }

    public $message = 'Error reading file. Make sure file is a valid CSV, ODD, or XLSX.';
}