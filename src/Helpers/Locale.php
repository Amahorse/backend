<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\App;
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
   * Ritorna un array di traduzioni per lingua corrente o specificata
   * Serve di passare le config opzionale come secondo parametro perchè gli serve la cartella custom dei json delle conversioni prima di fare il build delle config su Config.php
   *
   * @param string $locale
   * @param array  $config
   * @return array
   */
  public static function build(string $locale, $config = []):array {


    //Se non passata la lingua chiamo funzione detect che la riconosce
    if(!self::isActive($locale)) {
      throw new Error('Locale '.$locale.' is not active');
    }

    if(!empty($config) && !empty($config['dir']) && !empty($config['dir']['uploads'])) {
      Currencies::$directory = $config['dir']['uploads'];
    } 
       
    $values = self::$locales[$locale];

    //Metto la chiave nell'array
    $values['locale'] = $locale;

    //Defnisco tasso di conversione globale per classe price
    Price::$conversionRate = $values['currency']; 

    Price::$conversionRate = (float)Currencies::getConversionRate(Price::$databaseCurrency,$values['currency']); 

    //Set App Timezone
    date_default_timezone_set($values['timezone']);
    
    //Seto app locale in base a chiave configurazioni, cerca i vari locale in array dentro configurazioni app
    setlocale(LC_ALL, $locale . '.utf8');

    App::getInstance()->locale = $values;
    
    return $values;


  }


  /**
   * Controlla se una ligua è attiva nelle impostazioni
   *
   * @param string $locale
   * @return boolean
   */
  public static function isActive(string $locale): bool {

    self::$locales = require(_DIR_CONFIG_ . 'locale.php');
 
    if(is_array(self::$locales) && array_key_exists($locale,self::$locales)) {
      return true;
    }

    return false;
  }




}


?>