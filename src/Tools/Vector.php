<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Vector
{

    /**
     * Check if array is associative
     *
     * @param array $arr
     * @return boolean
     */
    public static function isAssociative(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }


/**
 * Equivalente di array merge ma preserva formattazione valori
 *
 * @param array $original
 * @param array $overwrite
 * @return array
 */
public static function overwrite(array $original, array $overwrite):array
{
    // Not included in function signature so we can return silently if not an array
    if (!is_array($overwrite)) {
        return $original;
    }
    if (!is_array($original)) {
        $original = $overwrite;
    }

    foreach($overwrite as $key => $value) {
        if (array_key_exists($key, $original) && is_array($value)) {
            self::overwrite($original[$key], $overwrite[$key]);
        } else {
            $original[$key] = $value;
        }
    }

    return $original;
}

/**
 * Ordina un array in base alle chiavi di un'altro array
 *
 * @param array $array
 * @param array $orderArray
 * @return array
 */
public static function sortArrayByArray(array $array, array $orderArray, $removeKeys = false):array {

    $ordered = array();

    //Se settato a true rimuove le chiavi dell'array ordinato e torna solo i valori esistenti nell'array di ordinamento
    if($removeKeys == true) {

        foreach ($orderArray as $key => $value) {

            if (array_key_exists($key, $array)) {
                $ordered[] = $array[$key];
                unset($array[$key]);
            }
        }
         
        return $ordered;

    }

    foreach ($orderArray as $key => $value) {

        if (array_key_exists($key, $array)) {
            $ordered[$key] = $array[$key];
            unset($array[$key]);
        }
    }

  
    return $ordered + $array;
}

}

?>