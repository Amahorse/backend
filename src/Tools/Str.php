<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Str
{


    /**
     * riconosce se una stringa inizia con una determinata porzione di stringa
     *
     * @param  string $haystack    Stringa
     * @param  string $needle      Valore da controllare
     * @return bool
     */
    public static function startsWith(mixed $haystack, mixed $needle): bool
    {

        if(!is_string($haystack) || is_null($haystack)) {
            return false;
        }
  
        return !strncmp($haystack, $needle, mb_strlen($needle));
    }

    /**
     * riconosce se una stringa finisce con una determinata porzione di stringa
     *
     * @param  string $haystack    Stringa
     * @param  string $needle      Valore da controllare
     * @return bool
     */
    public static function endsWith(mixed $haystack, mixed $needle): bool
    {

        if(!is_string($haystack) || is_null($haystack)) {
            return false;
        }

        $length = mb_strlen($needle);

        if ($length == 0) {
            return false;
        }

        return mb_substr($haystack, -$length) === $needle;
    }


    /**
     * riconosce se una stringa contiene un'altra
     *
     * @param  string $haystack    Stringa
     * @param  string $needle      Valore da controllare
     * @return bool
     */
    public static function contains(mixed $haystack, mixed $needle): bool
    {   

        if(empty($haystack) || empty($needle)) {
            return false;
        }

        return str_contains($haystack, $needle);
    }

    /**
     * Return a random string
     *
     * @param int $length
     * @return string
     */
    public static function random(int $length): string
    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Remove spaces, accent and special chars from a string
     *
     * @param string $string
     * @return string
     */
    public static function plain(string $string, $urlmode = false): string
    {

        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string); //ascii conversion

        $string = mb_strtolower($string); // lower and utf8 conversion

        $string = trim($string);

        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        if($urlmode) {
            return preg_replace('/[^A-Za-z0-9\-\/]/', '-', $string); // Removes all special chars but non / in urlmode.  
        }

        return preg_replace('/[^A-Za-z0-9\-]/', '-', $string); // Removes all special chars.
    }



    /**
     * Remove special chars from a string
     *
     * @param string $string
     * @return string
     */
    public static function sanitize(string $string): string
    {

        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string); //ascii conversion

        $string = trim($string);
    

        return preg_replace('/[^A-Za-z0-9\-]/', ' ', $string); // Removes all special chars.
    }



    /**
     * Convert a string to array
     *
     * @param string $string
     * @param string $delimiter
     * @return array
     */
    public static function toArray(string $string, string $delimiter = ";"): array
    {
        if ($ret = explode($delimiter, $string)) {
            return $ret;
        }

        return [];
    }

    /**
     * Trim spacese nad make lowercase all string
     *
     * @param string $string
     * @return string
     */
    public static function minify(string $string): string
    {
        return trim(mb_strtolower($string)); 
    }

    
}

?>