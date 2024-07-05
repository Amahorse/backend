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
  public static function build(string|bool $language = false): string|Error
  {
    // Se la lingua non è specificata, utilizza la lingua di default
    if (is_bool($language)) {
      $language = config('default', 'language');
    }


    if (!self::isActive($language)) {
      throw new Error('La lingua ' . $language . ' non è attiva');
    }

    $_ENV['language'] = $language;

    // Aggiungi file di traduzione generale all'array della lingua dopo aver assegnato la lingua all'istanza singleton dell'app

    // TODO: forse non serve
    // Translations::addFile('general');

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
