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

            if(!empty($property['id'])) {
                $property['id'] = (int) $property['id'];
            }

            if(!empty($property['required'] )) {
                $property['required'] = $property['required'] === 'true'? true: false;
            }

            if(isset($property['property'])) {
                if(!empty($property['property']['id'])) {
                    $property['property']['id'] = (int) $property['property']['id'];
                }
                if(!empty($property['property']['required'])) {
                    $property['property']['required'] = $property['property']['required'] === 'true'? true: false;
                }
            }

            if(!empty($property['fieldType']) && $property['fieldType'] === FieldCatalog::CUSTOM_OBJECT) {

                if(!empty($property['field']['customObject']['id'])) {
                    $property['field']['customObject']['id'] = (int) $property['field']['customObject']['id'];
                }

                if(!empty($property['field']['multiple'])) {
                    $property['field']['multiple'] = $property['field']['multiple'] === 'true'? true: false;
                }

                foreach ($property['field']['selectizeSearchResultProperties'] as &$selectizeSearchResultProperty) {
                    if(!empty($selectizeSearchResultProperty['id'] )) {
                        $selectizeSearchResultProperty['id'] = (int) $selectizeSearchResultProperty['id'];
                    }
                    if(!empty($selectizeSearchResultProperty['required'])) {
                        $selectizeSearchResultProperty['required'] = $selectizeSearchResultProperty['required'] === 'true'? true: false;
                    }
                }
            }

            $propertiesArray[] = $property;
        }

        return $propertiesArray;
    }
}