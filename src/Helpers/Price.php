<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Error;
use Kodelines\Tools\Number;
use Kodelines\Tools\Str;

class Price {

  public static $conversionRate = 1;

  //TODO: Super impostazioni fisse da mettere da qualche parte
  public static $databaseCurrency = 'EUR';

  public static $currency = 'EUR';

  public function __construct() {

    self::$currency = config('locale','currency');

    self::$conversionRate = Currencies::getConversionRate(self::$databaseCurrency,self::$currency);

  }

  /**
   * Set currency è una funzione che setta le variabili staticamente, devono essere cosi per tutto il sistema
   *
   * @param string $currency
   * @return void
   */
  public static function setCurrency(string $currency):void {

    if(!Currencies::get($currency)) {
      throw new Error('Currency ' . $currency . 'Not Found');
    }

    if($currency <> self::$databaseCurrency) {

      self::$conversionRate = Currencies::getConversionRate(self::$databaseCurrency,$currency);

    }

    self::$currency = $currency;


  }



  /**
   * Converte prezzo in valuta locale corrente
   *
   * @param mixed $price
   * @return float
   */
  public static function convert(mixed $price, int|float $conversionRate = 1):float {

    //Prezzo vuoto ritorna 0
    if(empty($price)) {
      return 0.00;
    }

    return self::format((float)$price * (float)$conversionRate);
  }

  /**
   * Mostra prezzo convertito e con simbolo valuta
   *
   * @param mixed $price
   * @return string
   */
  public static function toDecimal(mixed $price):string {
    return number_format((float)$price, 2, '.', '');
  }

  /**
   * Formatta prezzo in 0.00 
   *
   * @param int|float $price
   * @return float
   */
  public static function format(mixed $price) {

    if(is_null($price) || empty($price)) {
      $price = 0.00;
    }

    if(is_string($price)) {
      $price = (float)$price;
    }

    return (float)number_format(round($price,2 ,PHP_ROUND_HALF_EVEN), 2, '.', '');
  }

  /**
   * Formatta prezzi multipli su un array
   *
   * @param array $prices
   * @return array
   */
  public static function formatMultiple(array $prices):array {
  
    foreach($prices as $key => $value) {

      if(is_array($value)) {
        continue;
      }

      if(Str::startsWith($key,'price_') || Str::startsWith($key,'total_')) {

        $prices[$key] = self::toDecimal($value);
      }

    }

    return $prices;
  }

  //TODO: spostare su altra classe Bottle-Up
  /**
   * Ritorna l'importo della commissione pagamento
   *
   * @param float $price
   * @param float $commission
   * @return float
   */
  public static function paymentCommission(float $price,float $commission):float {

    if(!empty(floatval($commission))) {
      return Number::percentage($price,floatval($commission) * 100);
    }

    return 0.00;
  }
  
  /**
   * Ritorna il moltiplicatore tasse per calcolare la commissione pagamento
   *
   * @param float $tax
   * @param float $commission
   * @return float
   */
  public static function paymentMultiplier(float $tax, float $commission):float {
  
    if(!empty(floatval($commission))) {
  
      $float = floatval($commission);
  
      return ((1+($tax / 100)))/(1-$float*(1+($tax/100)));
  
    } else {
  
      return ((1+($tax / 100)));
  
    }
  

  }



  
  

}

?>