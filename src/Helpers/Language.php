<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Error;

class Language
{
  /**
   * Costruisce un array di traduzioni per la lingua corrente o specificata.
   *
   * @param string|bool $language
   * @return string|Error
   */
  public static function build(string|bool $language = false): string
  {
    // Se la lingua non è specificata, utilizza la lingua di default
    if(!$language) {
      $language = config('default', 'language');
    }


    if (!self::isActive($language)) {
      throw new Error('Language "' . $language . '" is not active');
    }

    return $language;
  }

  /**
   * Verifica se una lingua è attiva nelle impostazioni.
   *
   * @param string $language
   * @return bool
   */
  public static function isActive(string $language): bool
  {
    $appLanguage = config('app', 'languages');

    if (is_array($appLanguage) && in_array($language, $appLanguage)) {
      return true;
    }

    return ($appLanguage == $language) || false;
  }
}
