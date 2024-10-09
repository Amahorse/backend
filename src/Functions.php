<?php

use Kodelines\Db;
use Kodelines\Context;

if (!function_exists('config')) {
  /**
   * Retrieves a configuration value or group.
   *
   * @param string $type  Config group name or key.
   * @param string|bool $value Optional specific config value.
   * @return mixed False if not found, array if config group, or specific config value.
   */
  function config(string $type, string|bool $value = false): mixed {
    return !empty(Context::$config) ? Context::$config->get($type, $value) : false;
  }
}

/**
 * Retrieves a specific value from the client user context.
 *
 * This function checks if the 'client' function does not already exist, and if not,
 * it defines the 'client' function. The function attempts to retrieve a value from
 * the user context based on the provided key.
 *
 * @param string $value The key to retrieve from the user context.
 * @return mixed Returns the value associated with the provided key if the client context is not empty, otherwise returns false.
 */
if (!function_exists('client')) {

  function client(string $value): mixed {
    return !empty(Context::$token->client) && !empty(Context::$token->client[$value]) ? Context::$token->client[$value] : false;
  }
}


if (!function_exists('dev')) {
  /**
   * Checks if the Context is in development mode.
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

    $user = Context::$token->user ?? false;

    if (!$value) {
      return $user;
    }

    $userValue = $user[$value] ?? false;

    if ($sub) {
      return $userValue[$sub] ?? false;
    }

    return $userValue;
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
