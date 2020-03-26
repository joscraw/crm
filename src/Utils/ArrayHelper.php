<?php

namespace App\Utils;

/**
 * Trait ArrayHelper
 * @package App\Utils
 */
trait ArrayHelper
{
    protected function arrayHasDupes($array) {
        return count($array) !== count(array_unique($array));
    }

    protected function getKeysForDuplicateValues($my_arr, $clean = false, $includeInitialKeys = false, $caseSensitive = true) {
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

    protected function getArrayValuesRecursive($array) {
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

    protected function arrayFlatten($array) {
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
    protected function getValueByDotNotation($key, array $data, $default = null)
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

    protected function setValueByDotNotation(array &$arr, $path,$val)
    {
        $loc = &$arr;
        foreach(explode('.', $path) as $step)
        {
            $loc = &$loc[$step];
        }
        return $loc = $val;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function arrayMergeRecursive(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => & $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursive($merged[$key], $value);
            } else if (is_numeric($key)) {
                if (!in_array($value, $merged)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Returns duplicate values from array
     * @param $raw_array
     * @return array
     */
    protected function arrayNotUnique($raw_array) {
        $dupes = array();
        natcasesort($raw_array);
        reset($raw_array);

        $old_key   = NULL;
        $old_value = NULL;
        foreach ($raw_array as $key => $value) {
            if ($value === NULL) { continue; }
            if (strcasecmp($old_value, $value) === 0) {
                $dupes[$old_key] = $old_value;
                $dupes[$key]     = $value;
            }
            $old_value = $value;
            $old_key   = $key;
        }
        return $dupes;
    }

    /**
     * Checks if a given key is available in an array. If it is it returns that key
     * if not it increments and keeps checking until one is found that's available.
     * @param $array
     * @param $key
     * @return mixed
     */
    protected function determineKeyAvailability($array, $key) {
        if(!array_key_exists($key, $array)) {
            return $key;
        }
        return $this->determineKeyAvailability($array, ++$key);
    }
}