<?php 

/**
 * Funzioni che fanno da shortcut per funzioni database
 */

declare(strict_types=1);

use Kodelines\Db;

if (!function_exists('id')) {
    /**
     * Cast var 
     *
     * @method developmentMode
     * @return int
     */
   function id(mixed $id): int
    {

      if(is_bool($id)) {
        return 0;
      }

      return (int)filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    }
  
}

if (!function_exists('encode')) {
  /**
   * Cast var 
   *
   * @method developmentMode
   * @return mixed
   */
 function encode(mixed $string): mixed
  {
    return Db::encode($string);
  }

}

if (!function_exists('options')) {
  /**
   * Genera un array chiave->valore per valori enum del database da usare in input select
   *
   * @param string $table
   * @param string $field
   * @param array $extra
   * @return array
   */
 function options(string $table, string $field,$extra = []): array
  {
    return Db::enumOptions($table,$field,$extra);
  }

}

?>