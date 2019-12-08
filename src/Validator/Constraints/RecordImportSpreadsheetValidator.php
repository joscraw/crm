<?php

namespace App\Validator\Constraints;

use App\Service\PhpSpreadsheetHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class RecordImportSpreadsheetValidator
 * @package App\Validator\Constraints
 */
class RecordImportSpreadsheetValidator extends ConstraintValidator
{
    /**
     * @var PhpSpreadsheetHelper;
     */
    private $phpSpreadsheetHelper;

    /**
     * RecordImportSpreadsheetValidator constructor.
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     */
    public function __construct(PhpSpreadsheetHelper $phpSpreadsheetHelper)
    {
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
    }

    /**
     * @param UploadedFile $file
     * @param Constraint $constraint
     */
    public function validate($file, Constraint $constraint)
    {
        // Let's let PHPSpreadsheet validate it for us and try to load the columns from the spreadsheet
        // If it can't we aren't going to be able to load the file in anyways to import so let's just
        // throw an error back to the end user
        if(!$this->phpSpreadsheetHelper->getColumnNames($file)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}