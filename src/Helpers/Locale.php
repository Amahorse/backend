<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Helpers\Currencies;
use Kodelines\Helpers\Price;
use Kodelines\Error;

class Locale
{

  /**
   * Variabile statica che contiene i locale
   *
   * @var array
   */
  public static $locales = array();


  /**
   * Locale attualmente buildato
   *
   * @var array
   */
  public static $current = array();



  /**
   * Ritorna un array di traduzioni per lingua corrente o specificata
   * Serve di passare le config opzionale come secondo parametro perchè gli serve la cartella custom dei json delle conversioni prima di fare il build delle config su Config.php
   *
   * @param string $locale
   * @param array  $config
   * @return array
   */
  public static function build(string|bool $locale):string {


    // Se la lingua non è specificata, utilizza la lingua di default
    if(!$locale) {
      $locale = config('default', 'locale');
    }


    //Chiamo sempre prima is active cosi da avere i locale caricati su self::$locales
    if(!self::isActive($locale)) {
      throw new Error('Locale '.$locale.' is not active');
    }

    self::$current = self::locales()[$locale];

    //TODO: valuta su database e cron per aggiornare 
    /*
    if(!empty($config) && !empty($config['dir']) && !empty($config['dir']['uploads'])) {
      Currencies::$directory = $config['dir']['uploads'];
    } 
       
    $values = self::$locales[$locale];

    //Metto la chiave nell'array
    $values['locale'] = $locale;

    //Defnisco tasso di conversione globale per classe price
    Price::$conversionRate = $values['currency']; 

    Price::$conversionRate = (float)Currencies::getConversionRate(Price::$databaseCurrency,$values['currency']); 
    */

    //Set App Timezone
    date_default_timezone_set(self::$current['timezone']);
    
    //Seto app locale in base a chiave configurazioni, cerca i vari locale in array dentro configurazioni app
    setlocale(LC_ALL, self::$current['lcid'] . '.utf8');
    
    return $locale;

  }


  /**
   * Controlla se una ligua è attiva nelle impostazioni
   *
   * @param string $locale
   * @return boolean
   */
  public static function isActive(string $locale): bool {
 
    if(is_array(self::locales()) && array_key_exists($locale,self::locales())) {
      return true;
    }

    return false;
  }


  public static function locales():array {

    if(empty(self::$locales)) {
      self::$locales = config('locales');
    }

    return self::$locales;
  }


}


?>