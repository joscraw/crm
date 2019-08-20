<?php

namespace App\Utils;

use App\Model\FieldCatalog;

/**
 * Trait PropertyHelper
 * @package App\Utils
 */
trait PropertyHelper
{
    /**
     * When property data is submitted in an ajax request
     * the integers and booleans get quoted. This causes
     * unexpected results and messes up the denormalizing of the data
     * which expects integers and actual booleans for these properties
     *
     * @param $properties
     * @return array
     */
    private function setValidPropertyTypes($properties) {

        $propertiesArray = [];

        foreach ($properties as &$property) {

            $property['id'] = (int) $property['id'];
            $property['required'] = $property['required'] === 'true'? true: false;

            if(isset($property['property'])) {
                $property['property']['id'] = (int) $property['property']['id'];
                $property['property']['required'] = $property['property']['required'] === 'true'? true: false;
            }

            if($property['fieldType'] === FieldCatalog::CUSTOM_OBJECT) {

                $property['field']['customObject']['id'] = (int) $property['field']['customObject']['id'];
                $property['field']['multiple'] = $property['field']['multiple'] === 'true'? true: false;

                foreach ($property['field']['selectizeSearchResultProperties'] as &$selectizeSearchResultProperty) {
                    $selectizeSearchResultProperty['id'] = (int) $selectizeSearchResultProperty['id'];
                    $selectizeSearchResultProperty['required'] = $selectizeSearchResultProperty['required'] === 'true'? true: false;
                }
            }

            $propertiesArray[] = $property;
        }

        return $propertiesArray;
    }
}