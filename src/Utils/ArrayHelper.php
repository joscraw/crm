<?php

namespace App\Utils;

/**
 * Trait ArrayHelper
 * @package App\Utils
 */
trait ArrayHelper
{
    private function arrayHasDupes($array) {
        return count($array) !== count(array_unique($array));
    }

    private function getKeysForDuplicateValues($my_arr, $clean = false, $includeInitialKeys = false, $caseSensitive = true) {
        if ($clean) {
            return array_unique($my_arr);
        }

        if($caseSensitive) {
            $my_arr = array_map('strtolower', $my_arr);
        }

        $dups = $new_arr = array();
        foreach ($my_arr as $key => $val) {
            if (!isset($new_arr[$val])) {
                $new_arr[$val] = $key;
            } else {
                if (isset($dups[$val])) {
                    $dups[$val][] = $key;
                } else {
                    if($includeInitialKeys) {
                        $dups[$val] = array($new_arr[$val], $key);
                    } else {
                        $dups[$val] = array($key);
                    }
                }
            }
        }
        return $dups;
    }

    public function getArrayValuesRecursive($array) {
        $flat = array();

        foreach($array as $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, $this->getArrayValuesRecursive($value));
            }
            else {
                $flat[] = $value;
            }
        }
        return $flat;
    }

    public function arrayFlatten($array) {
        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->arrayFlatten($value));
            }
            else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}