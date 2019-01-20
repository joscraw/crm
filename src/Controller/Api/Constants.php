<?php

namespace App\Controller\Api;

/**
 * Constants
 *
 * Consolidates constant strings used across API controllers.
 *
 */
class Constants
{
    const ERR_MSG_MISSING_ARG = 'Missing argument: ';
    const ERR_MSG_CUSTOM_OBJECT_NOT_FOUND = 'Custom object not found.';
    const ERR_MSG_PROPERTY_NOT_FOUND = 'Property not found.';

    const ARG_CUSTOM_OBJECT_ID = 'custom_object_id';
    const ARG_PROPERTY_ID = 'property_id';

    const ERROR_CODE_UNKNOWN = 999;
    const ERROR_CODE_MISSING_ARG = 888;
    const ERROR_CODE_NOT_FOUND_CUSTOM_OBJECT = 4042;
    const ERROR_CODE_NOT_FOUND_PROPERTY = 5052;
}