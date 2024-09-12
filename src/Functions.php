<?php

namespace Kodelines;

use Kodelines\Db;

if (!function_exists('dev')) {
  /**
   * Shortcut to check if system is in development mode based on app config 
   *
   * @method dev
   * @return bool
   */
  function dev(): bool
  {
    return !empty($_ENV['config']) && config('app', 'development_mode');
  }
}

if (!function_exists('dump')) {
  /**
   * Fa morire il sistema facendo il dump di un valore (da utilizzare a scopo di debug)
   *
   * @method dump
   * @return void
   */
  function dump(mixed $value): void
  {
    die(var_dump($value));
  }
}


if (!function_exists('config')) {
  /**
   * Shortcut to get a config group or a single group from loaded config vars
   *
   * @method config
   * @param  string         $group   Config group name (key of json), in case of .php config is the name of the php file (no extension)
   * @param  string|boolean $value   Optional config group value
   * @return mixed            False if not found, array if config group or value if config value
   */
  function config(string $type, string|bool $value = false): mixed {
    return !empty($_ENV['config']) ? $_ENV['config']->get($type, $value) : false;
  }
}




if (!function_exists('language')) {
  /**
   * Shortcut to get app language
   *
   * @return string
   */
  function language(): string
  {
    return $_ENV['language'];
  }
}


if (!function_exists('user')) {
  /**
   * Ritorna valori utente in sessione, può tornare false se utente non loggato, tutto l'array di valori o il singolo specificato
   *
   * @param string $value
   * @param string|bool $sub
   * @return mixed
   */
  function user($value = null, $sub = false): mixed
  {
    if (empty($_ENV['user'])) {
      return false;
    }

    if (!$value) {
      return $_ENV['user'];
    }

    if (!isset($_ENV['user'][$value])) {
      return false;
    }

    if ($sub && !isset($_ENV['user'][$value][$sub])) {
      return false;
    }

    return $sub ? $_ENV['user'][$value][$sub] : $_ENV['user'][$value];
  }
}

  
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


