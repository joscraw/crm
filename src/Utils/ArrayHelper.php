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

    /**
     * @see https://selvinortiz.com/blog/traversing-arrays-using-dot-notation
     * @param $key
     * @param array $data
     * @param null $default
     * @return array|mixed|null
     */
    function getValueByDotNotation($key, array $data, $default = null)
    {
        // @assert $key is a non-empty string
        // @assert $data is a loopable array
        // @otherwise return $default value
        if (!is_string($key) || empty($key) || !count($data))
        {
            return $default;
        }

        // @assert $key contains a dot notated string
        if (strpos($key, '.') !== false)
        {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey)
            {
                // @assert $data[$innerKey] is available to continue
                // @otherwise return $default value
                if (!array_key_exists($innerKey, $data))
                {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        // @fallback returning value of $key in $data or $default value
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }
}