<?php

declare(strict_types=1);

namespace Elements\Store\Helpers;

use Kodelines\Helpers\Price as PriceCore;
use Kodelines\Tools\Number;

class Price extends PriceCore  {


    /**
     * Variabili finali prezzo singola unità del prodotto
     */
    private static $vars = array(
        "price" => 0.00,
        //Valore sconto, può essere ambiguo, su prezzo b2c è su price_to_pay mentre su b2b è su price_taxes_excluded
        "price_discount" =>  0.00,
        //Il prezzo da pagare è sempre comprensivo di tasse
        "discount_percentage" => 0.00,
        "discount_offer_percentage" => 0.00,  //Sconto globale su sito
        "discount_product_percentage" => 0.00, //Sconto su prodotto
        "discount_client_percentage" => 0.00,    //Sconto coupon su carrello (può essere cumulabile o meno in base a parametro "discount_cumulable" settato su questa classe)
        "discount_contract_percentage" => 0.00,
        "discount_final_percentage" => 0.00,
        //Vari tipi di sconto,
        "total_price" => 0.00,
        "total_discount" => 0.00,
        "total_to_pay" => 0.00
    );
        

    /**
     * Funzione finale che inizia i calcoli, ritorna un merge tra i totali e i vars
     *
     * @return array
     */
    public static function calculate(array $values, int $quantity = 1):array {

      
      //Assegno valori base a calculated
      $calculated = self::$vars;

      $calculated['price'] = $values['price'];

      //Sconto cliente e sconto prodotto
      if(!empty($values['discount_client_percentage']) || !empty($values['discount_product_percentage'])) {
        
        if($values['discount_client_percentage'] < $values['discount_product_percentage']) { 
            $calculated['discount_percentage'] = $values['discount_client_percentage'];
        } else {     
            $calculated['discount_percentage'] = $values['discount_product_percentage'];
        }

      }

      //Sconto offerta maggiore di sconto cliente lo sovrascrive
      if(!empty($values['discount_offer_percentage']) && $values['discount_offer_percentage'] > $calculated['discount_percentage']) {
        $calculated['discount_percentage'] = $values['discount_offer_percentage'];
      }

      if(!empty($values['discount_contract_percentage']) && $values['discount_contract_percentage'] > $calculated['discount_percentage']) {
        $calculated['discount_percentage'] = $values['discount_contract_percentage'];
      }

      if(!empty($calculated['discount_percentage'])) {
        $calculated['price_discount'] = Number::percentage($calculated['price'],$calculated['discount_percentage']);
      }


      $calculated["total_price"] = $calculated['price'] * $quantity;

      $calculated["total_to_pay"] = $calculated['total_price'];

      
      if(!empty($calculated['discount_percentage'])) {

        $calculated["total_discount"] = Number::percentage($calculated['total_price'],$calculated['discount_percentage']);

        $calculated["total_to_pay"] -= $calculated['total_discount'];

      }
     
      
      return Price::formatMultiple($calculated);


    }




}

?>