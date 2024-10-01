<?php

use Kodelines\Db;
use Kodelines\System;

if (!function_exists('config')) {
  /**
   * Retrieves a configuration value or group.
   *
   * @param string $type  Config group name or key.
   * @param string|bool $value Optional specific config value.
   * @return mixed False if not found, array if config group, or specific config value.
   */
  function config(string $type, string|bool $value = false): mixed {
    return !empty(System::$config) ? System::$config->get($type, $value) : false;
  }
}

if (!function_exists('dev')) {
  /**
   * Checks if the system is in development mode.
   *
   * @return bool True if in development mode, false otherwise.
   */
  function dev(): bool {
    return config('app', 'development_mode');
  }
}

if (!function_exists('dump')) {
  /**
   * Dumps a value and terminates the script (for debugging purposes).
   *
   * @param mixed $value The value to dump.
   * @return void
   */
  function dump(mixed $value): void {
    die(var_dump($value));
  }
}

if (!function_exists('user')) {
  /**
   * Retrieves user session values.
   *
   * @param string|null $value Optional specific user value.
   * @param string|bool $sub Optional sub-value.
   * @return mixed False if user not logged in or value not found, otherwise the user value.
   */
  function user(string $value = null, string|bool $sub = false): mixed {
    if (empty(System::$user)) {
      return false;
    }

    if (!$value) {
      return System::$user;
    }

    if (!isset(System::$user[$value])) {
      return false;
    }

    if ($sub && !isset(System::$user[$value][$sub])) {
      return false;
    }

    return $sub ? System::$user[$value][$sub] : System::$user[$value];
  }
}

if (!function_exists('id')) {
  /**
   * Casts a variable to an integer, sanitizing it.
   *
   * @param mixed $id The variable to cast.
   * @return int The sanitized integer.
   */
  function id(mixed $id): int {
    if (is_bool($id)) {
      return 0;
    }

    return (int)filter_var($id, FILTER_SANITIZE_NUMBER_INT);
  }
}

if (!function_exists('encode')) {
  /**
   * Encodes a string using the database encoding method.
   *
   * @param mixed $string The string to encode.
   * @return mixed The encoded string.
   */
  function encode(mixed $string): mixed {
    return Db::encode($string);
  }
}

if (!function_exists('options')) {
  /**
   * Generates an array of key-value pairs for enum values from the database.
   *
   * @param string $table The database table.
   * @param string $field The field in the table.
   * @param array $extra Optional extra parameters.
   * @return array The key-value pairs for the enum values.
   */
  function options(string $table, string $field, array $extra = []): array {
    return Db::enumOptions($table, $field, $extra);
  }
}
