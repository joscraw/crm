<?php

namespace App\Utils;

/**
 * Trait MultiDimensionalArrayExtractor
 * @package App\Utils
 */
trait MultiDimensionalArrayExtractor
{
    /**
     * @param $array
     * @param array $values
     * @return array
     */
    public function extractValues($array, $values = []){
        foreach($array as $key => $value){
            //If $value is an array.
            if(is_array($value)){
                //We need to loop through it.
                $values = $this->extractValues($value, $values);
            } else{
                $values[] = $value;
            }
        }
        return $values;
    }

    /**
     * @param $array
     * @param array $keys
     * @return array
     */
    function extractKeys($array, $keys = []){
        $keys = [];
        foreach($array as $key => $value){
            //If $value is an array.
            if(is_array($value)){
                //We need to loop through it.
                $keys = $this->extractKeys($value, $keys);
            } else{
                $keys[] = $key;
            }
        }
        return $keys;
    }
}