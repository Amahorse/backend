<?php

declare(strict_types=1);

namespace Elements\Store\Helpers;

use Kodelines\Db;
use Kodelines\Helpers\Price as PriceCore;
use Kodelines\Tools\Number;

class Price extends PriceCore  {

    /**
     * Quantità per totali
     *
     * @var integer
     */
    public int $quantity;


    /**
     * Array che contiene i valori finali calcolati
     *
     * @var array
     */
    public array $calculated = [];


    /**
     * Variabili finali prezzo singola unità del prodotto
     */
    private $vars = array(
        "price_final" => 0.00,
        //Fee generali store
        "price_fee_fixed" => 0.00,
        "price_fee_percentage" => 0.00,
        "price_fee" => 0.00,
        //Vari tipi di sconto
        "discount_percentage" => 0.00,
        "discount_offer_percentage" => 0.00,  //Sconto globale su sito
        "discount_product_percentage" => 0.00, //Sconto su prodotto
        "discount_client_percentage" => 0.00,    //Sconto coupon su carrello (può essere cumulabile o meno in base a parametro "discount_cumulable" settato su questa classe)
        "discount_contract_percentage" => 0.00,
        "discount_final_percentage" => 0.00,
        //Valore sconto, può essere ambiguo, su prezzo b2c è su price_to_pay mentre su b2b è su price_taxes_excluded
        "price_discount" =>  0.00,
        //Il prezzo da pagare è sempre comprensivo di tasse
        "price_to_pay" => 0.00
    );
        
    /**
     * Variabili totali prezzo prodotto, moltiplica e formatta solo alla fine per arrotondamenti
     *
     * @var array
     */
    private $totals = array(
        "total_price_final" => 0.00,
        "total_to_pay" => 0.00,
        "total_discount" => 0.00
    );


    /**
     * Funzione finale che inizia i calcoli, ritorna un merge tra i totali e i vars
     *
     * @return array
     */
    public function calculate(array $values, int $quantity = 1):array {


      //Assegno quantità ma prima faccio controllo su empty quantità per non dare errore a 0 o null e la resetto
      if(!empty($quantity)) {
        $this->quantity = $quantity;
      } else {
        $this->quantity = 1;
      }
      
      //Assegno valori base a calculated
      $this->calculated = $this->vars;

      $this->calculated['price_final'] = $values['price'];

      //Sconto cliente e sconto prodotto
      if(!empty($values['discount_client_percentage']) || !empty($values['discount_product_percentage'])) {
        
        if($values['discount_client_percentage'] < $values['discount_product_percentage']) { 
            $this->calculated['discount_percentage'] = $values['discount_client_percentage'];
        } else {     
            $this->calculated['discount_percentage'] = $values['discount_product_percentage'];
        }

      }

      //Sconto offerta maggiore di sconto cliente lo sovrascrive
      if(!empty($values['discount_offer_percentage']) && $values['discount_offer_percentage'] > $this->calculated['discount_percentage']) {
        $this->calculated['discount_percentage'] = $values['discount_offer_percentage'];
      }

      if(!empty($values['discount_contract_percentage']) && $values['discount_contract_percentage'] > $this->calculated['discount_percentage']) {
        $this->calculated['discount_percentage'] = $values['discount_contract_percentage'];
      }

      $this->calculated['price_discount'] = Number::percentage($this->calculated['price_final'],$this->calculated['discount_percentage']);

      
      return Price::formatMultiple($this->calculated);


    }



  /**
   * Fixa i prezzi prima di ritornarli facendo il cast a float
   *
   * @param array $values
   * @return array
   */
  public function fix(array $values):array
  {

    foreach($values as $key => $value) {

        if(isset($this->vars[$key]) || isset($this->totals[$key])) {
            $values[$key] = (float)$value;
        }

    }

    return $values;
  }


}

?>