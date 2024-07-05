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
     * Tipo cliente per tasse e varie altre cose
     *
     * @var string
     */
    public string $type = 'b2c';


    /**
     * Array che contiene i valori iniziali del prodotto
     *
     * @var array
     */
    public array $values = [];

    /**
     * Array che contiene i valori finali calcolati
     *
     * @var array
     */
    public array $calculated = [];


    /**
     * Sconto globale da applicare a tutti i calcoli
     *
     * @var float
     */
    public float $discount = 0.00;

    /**
     * Definisce se lo sconto a carrello è cumulabile
     *
     * @var boolean
     */
    public bool $discount_cumulable = false;


    /**
     * Array contentente commissione agente, recuperate in construct se sattato header IdAgents
     *
     * @var array
     */
    public $commissions = [];

    //Variabili iniziali che possono essere sovrascritte
    public $baseVars = array(
        "price_taxes" =>  0.00,
        "price_taxes_excluded" =>  0.00,
        "tax" =>  0.00,
        "discounts_global_percentage" => 0.00,  //Sconto globale su sito
        "discounts_product_percentage" => 0.00, //Sconto su prodotto
        "price_reseller_recharge_percentage" => 0.00,
        "price_reseller_recharge_percentage_product" => 0.00,
        "price_reseller_marketing_percentage" => 0.00,
        "price_store_recharge_percentage" => 0.00,
        "price_agent_commission_percentage" => 0.00,
        //Variabili endchain configuratore devono rimanere 
        "price_buy" =>  0.00,
        "price_capsule" =>  0.00,
        "price_shipping_adjustment" =>  0.00,
        "price_reseller_shipping_adjustment" => 0.00,
        "price_reseller_shipping_adjustment_endchain" =>  0.00,
        "price_free_port" =>  0.00,
        "price_front_label" =>  0.00,
        "price_retro_label" =>  0.00,
        "price_packaging" =>  0.00,
        "price_processing" =>  0.00,
        "price_end_chain" =>  0.00,
        "price_fee_fixed" => 0.00,
        "price_fee_percentage" => 0.00,
        "price_fee" => 0.00,
        "price_recharge" =>  0.00,
        "price_recharge_percentage" =>  0.00,
    );

    /**
     * Variabili finali prezzo singola unità del prodotto
     */
    private $vars = array(
        //prezzo base iva esclusa, può venire fuori da campo fisso su store_products o prezzo ricaricato da configuratore
        "price_base" =>  0.00,
        //Ricarico
        "price_recharge" => 0.00,
        //Prezzo del venditore escluso di ricarichi reseller
        "price_vendor" => 0.00,
        //Ricarico reseller (applicato al price_taxes_excluded iniziale prima di definire tasse etc)
        "price_reseller_recharge" => 0.00,
        "price_reseller_recharge_final" => 0.00,
        "price_reseller_recharge_percentage" => 0.00,
        "price_reseller_recharge_percentage_product" => 0.00,
        "price_reseller_recharge_percentage_total" => 0.00,
        //Prezzo ricarichi per store
        "price_store_recharge" => 0.00,
        "price_store_recharge_final" => 0.00,
        "price_store_recharge_percentage" => 0.00,
        //Le fee per il reseller vengono applicate al prezzo iva esclusa finale (fee che vendono prese dal sistema)
        "price_reseller_fee" => 0.00,
        "price_reseller_fee_percentage" => 0.00,
        "price_reseller_shipping_adjustment" => 0.00,
        "price_reseller_shipping_adjustment_endchain" => 0.00,
        "price_reseller_marketing_percentage" => 0.00,
        "price_reseller_marketing" => 0.00,
        //Come le fee le commissioni agente vengogno prese alla fine ma in questo caso vengono date all'agente
        "price_agent_commission" => 0.00,
        "price_agent_commission_percentage" => 0.00,
        //Vars of Taxes
        "tax" =>  0.00,
        "tax_multiplier" =>  0.00,
        "price_taxes" =>  0.00,
        "price_taxes_excluded" =>  0.00,
        "price_payment_commission" =>  0.00,
        //Price final is the final product price with taxes and no discounts
        "price_final" => 0.00,
        "price_final_taxes_excluded" => 0.00,
        "price_final_taxes" => 0.00,
        //Fee generali store
        "price_fee_fixed" => 0.00,
        "price_fee_percentage" => 0.00,
        "price_fee" => 0.00,
        //Vari tipi di sconto
        "discounts_global_percentage" => 0.00,  //Sconto globale su sito
        "discounts_product_percentage" => 0.00, //Sconto su prodotto
        "discounts_cart_percentage" => 0.00,    //Sconto coupon su carrello (può essere cumulabile o meno in base a parametro "discount_cumulable" settato su questa classe)
        "discounts_total_percentage" => 0.00,
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
        "total_price_taxes_excluded" =>  0.00,
        "total_price_final" => 0.00,
        "total_price_final_taxes_excluded" => 0.00,
        "total_price_final_taxes" => 0.00,
        "total_price_vendor" => 0.00,
        "total_reseller_fee" => 0.00,
        "total_reseller_recharge" => 0.00,
        "total_reseller_shipping_adjustment" => 0.00,
        "total_reseller_marketing" => 0.00,
        "total_store_recharge" => 0.00,
        "total_agent_commission" => 0.00,
        "total_recharge" => 0.00,
        "total_taxes" => 0.00,
        "total_to_pay" => 0.00,
        "total_discount" => 0.00,
        "total_payment_commission" => 0.00,
        "total_fee" => 0.00
    );


    /**
     * Funzione finale che inizia i calcoli, ritorna un merge tra i totali e i vars
     *
     * @return array
     */
    public function calculate(array $values, int $quantity = 1, $convert = true):array {

      //Setto un array con i valori iniziali
      $this->values = $values;

      //Assegno quantità ma prima faccio controllo su empty quantità per non dare errore a 0 o null e la resetto
      if(!empty($quantity)) {
        $this->quantity = $quantity;
      } else {
        $this->quantity = 1;
      }
      


      //Assegno valori base a calculated
      $this->calculated = $this->vars;

      
      //Faccio un ciclo delle vars su $values per avere settate su calculated il prezzo iva esclusa (obbligatorio sennò torna tutto a 0, gli sconti e le commissioni) 
      foreach($this->baseVars as $key => $value) {
        if(isset($values[$key]) && $values[$key] !== null) {
            $this->calculated[$key] = (float)$values[$key];
        }
      }
      


        //NB GIGANTESCO: Il price_base viene salvato in euro, tutto quello che c'è prima è in euro, dal prezzo iva esclusa compreso in poi può essere convertito
        if(Price::$conversionRate <> 1 && $convert == true) {         
            $this->calculated['price_taxes_excluded'] = Price::format($this->calculated['price_taxes_excluded'] * Price::$conversionRate);
        } 

        $this->calculated['price_base'] = $this->calculated['price_taxes_excluded'];

        //OPZIONE 1: Se è vuoto prezzo iva esclusa torno subito totali a 
        if(empty($this->calculated['price_taxes_excluded'])) {

            return $this->totals();
            
        } 
    
    
      //QUA HO SEMPRE UN price_taxes_excluded i calcoli successivi vengono fatti sempre sul prezzo iva esclusa 

      //STEP 2: Controllo se c'è un ricarico reseller, se c'è lo applico al prezzo iva esclusa 
      if(!empty($this->calculated['price_reseller_recharge_percentage']) || !empty($this->calculated['price_store_recharge_percentage']) || !empty($this->calculated['price_reseller_recharge_percentage_product'])) {

    

        //Sommo percentuale ricarico prodotto + base reseller
        $this->calculated['price_reseller_recharge_percentage_total'] = $this->calculated['price_reseller_recharge_percentage'] + $this->calculated['price_reseller_recharge_percentage_product'];

        if(!empty($this->calculated['price_reseller_recharge_percentage_total'])) {

            $this->calculated['price_reseller_recharge'] = Number::percentage($this->calculated['price_base'], $this->calculated['price_reseller_recharge_percentage_total']);

            //Ottengo nuovo prezzo iva esclusa aggiungendo il ricarico del reseller
            $this->calculated['price_taxes_excluded'] = $this->calculated['price_base'] + $this->calculated['price_reseller_recharge'];

            //Ricarico finale reseller prima di applicare sconti
            $this->calculated['price_reseller_recharge_final'] = $this->calculated['price_reseller_recharge'];
        
        }

        if(!empty($this->calculated['price_store_recharge_percentage'])) {
           
            $this->calculated['price_store_recharge'] = Number::percentage($this->calculated['price_base'],$this->calculated['price_store_recharge_percentage']);

            //Ricarico finale reseller prima di applicare sconti
            $this->calculated['price_store_recharge_final'] = $this->calculated['price_store_recharge'];
       
        }


        //Ottengo nuovo prezzo iva esclusa aggiungendo il ricarico del reseller e dello store
        $this->calculated['price_taxes_excluded'] = $this->calculated['price_base'] + $this->calculated['price_reseller_recharge'] +  $this->calculated['price_store_recharge'];

        if(!empty($this->calculated['price_reseller_shipping_adjustment'])) {

            if(Price::$conversionRate <> 1 && $convert == true) {
                $this->calculated['price_reseller_shipping_adjustment'] = Price::format($this->calculated['price_reseller_shipping_adjustment'] * Price::$conversionRate);
            }

            $this->calculated['price_taxes_excluded'] += $this->calculated['price_reseller_shipping_adjustment'];
        }

        if(!empty($this->calculated['price_reseller_marketing_percentage'])) {

            $this->calculated['price_reseller_marketing'] = Number::percentage($this->calculated['price_taxes_excluded'],$this->calculated['price_reseller_marketing_percentage']);

            $this->calculated['price_taxes_excluded'] += $this->calculated['price_reseller_marketing'];

        }



      }

      
       //Qui il vecchio price_taxes_escluded e price_to_pay vengono sovrascritti aggiungendo tasse al prezzo ricaricato
       $this->addTaxes(); 

      //STEP 3: A questo punto ho un prezzo finale senza sconti che rimane fisso fine alla fine, dipende ta tipo cliente
      $this->calculated['price_final'] = $this->calculated['price_to_pay'];

      $this->calculated['price_final_taxes_excluded'] = $this->calculated['price_taxes_excluded'];

      $this->calculated['price_final_taxes'] = $this->calculated['price_taxes'];
    
      //STEP 4: Chiamo funzione per trovare eventuali sconti che si applicano al price_final, gli vanno passati i valori originali del prodotto per controlli su minimi ordine
      $this->discounts();

      //STEP 5: Se il prezzo è scontato, ridichiaro un nuovo prezzo finale e devo ricalcolare le tasse ed il resto
      if(!empty($this->calculated['discounts_total_percentage'])) {
        
  
        //A questo punto calcolo gli sconti ma se è b2c va applicato lo sconto al prezzo paypal 
        if($this->type == 'b2c') {

            //Mi calcolo l'ammontare dello sconto 
            $this->calculated['price_to_pay'] = Number::removePercentage($this->calculated['price_final'],$this->calculated['discounts_total_percentage']);

            //Sul b2c ho un nuovo prezzo di vendita finale già incluso di tutte le commissioni e iva 
            $this->calculated['price_discount'] = $this->calculated['price_final'] - $this->calculated['price_to_pay'];
           
            //Calcolo tasse solo per reportistica
            $this->calcTaxesB2C();



        } else {

            //Mi calcolo l'ammontare dello sconto 
            $this->calculated['price_taxes_excluded'] = Number::removePercentage($this->calculated['price_final_taxes_excluded'],$this->calculated['discounts_total_percentage']);

            //B2B toglie semplicemente lo sconto dal prezzo iva esclusa trovato in precedenza  
            $this->calculated['price_discount'] = $this->calculated['price_final_taxes_excluded'] - $this->calculated['price_taxes_excluded'];
       
            //Riaggiungo le tasse al nuovo prezzo iva esclusa 
            $this->addTaxes();

        }
        
       
        //Lo sconto a quessto punto viene applicato anche alle variabili reseller se applicati
        if(!empty($this->calculated['price_reseller_recharge'])) {
            $this->calculated['price_reseller_recharge'] = $this->calculated['price_reseller_recharge'] - Number::percentage($this->calculated['price_reseller_recharge'],$this->calculated['discounts_total_percentage']);
        }

        if(!empty($this->calculated['price_store_recharge'])) {
            $this->calculated['price_store_recharge'] = $this->calculated['price_store_recharge'] - Number::percentage($this->calculated['price_store_recharge'],$this->calculated['discounts_total_percentage']);
        }
       

        if(!empty($this->calculated['price_reseller_shipping_adjustment'])) {
            $this->calculated['price_reseller_shipping_adjustment'] = $this->calculated['price_reseller_shipping_adjustment'] - Number::percentage($this->calculated['price_reseller_shipping_adjustment'],$this->calculated['discounts_total_percentage']);
        }

        if(!empty($this->calculated['price_reseller_marketing'])) {
            $this->calculated['price_reseller_marketing'] = $this->calculated['price_reseller_marketing'] - Number::percentage($this->calculated['price_reseller_marketing'],$this->calculated['discounts_total_percentage']);
        }

      }

       

      //Calcolo commissioni
      $this->commissions();

 
      
      return Price::formatMultiple($this->totals());


    }

    /**
     * Aggiunge le tasse al prezzo iva esclusa
     *
     * @return void
     */
    public function addTaxes() {


        if(!empty($this->calculated['tax'])) {

            if($this->type == 'b2c') {

                $this->calculated['tax_multiplier'] = Price::paymentMultiplier($this->calculated['tax'],config('store','payment_commission_percentage'));

                $this->calculated['price_to_pay'] = Number::format($this->calculated['price_taxes_excluded'] * $this->calculated['tax_multiplier']);

                $this->calculated['price_taxes'] = $this->calculated['price_to_pay'] - Number::format($this->calculated['price_to_pay'] / (1 + (($this->calculated['tax'] / 100))));
 
                //Calcolo la commissione paypal sul prezzo di pagamento finale (e nel 99% dei casi si bestemmia perchè non va)
                $this->calculated['price_payment_commission'] = Number::format($this->calculated['price_to_pay'] * config('store','payment_commission_percentage'));

                $this->calculated['price_taxes_excluded'] = $this->calculated['price_to_pay'] - $this->calculated['price_taxes'];
                
           
            } else {

                //Calculate taxes normally
                $this->calculated['price_taxes'] = Number::percentage($this->calculated['price_taxes_excluded'],$this->calculated['tax']);

                $this->calculated['price_to_pay'] = $this->calculated['price_taxes_excluded'] + $this->calculated['price_taxes'];

            }


        } else {

            $this->calculated['price_to_pay'] = $this->calculated['price_taxes_excluded'];

        }

        return $this;

    }


    /**
     * Calcola le tasse a ritroso sul prezzo da pagare B2C
     *
     * @return void
     */
    public function calcTaxesB2C() {

        //Il calcolo tasse inverso fa lo scorporo dell'iva
        if(!empty($this->calculated['tax'])) {

            //Scorporo iva da prezzo di vendita
            $this->calculated['price_taxes'] = $this->calculated['price_to_pay'] - Number::format((($this->calculated['price_to_pay'] * 100) / ($this->calculated['tax'] + 100)));

            if($this->type == 'b2c') {

                $this->calculated['tax_multiplier'] = Price::paymentMultiplier($this->calculated['tax'],config('store','payment_commission_percentage'));

                $this->calculated['price_taxes_excluded'] = $this->calculated['price_to_pay'] - $this->calculated['price_taxes'];

                $this->calculated['price_payment_commission'] = Number::format($this->calculated['price_to_pay'] * config('store','payment_commission_percentage'));

            } else {

                $this->calculated['price_taxes_excluded'] = $this->calculated['price_to_pay'] - $this->calculated['price_taxes'];

            }

        } else {

            //Qui fa la cosa di prima all'inverso
            $this->calculated['price_taxes_excluded'] = $this->calculated['price_to_pay'];

        }

        return $this;

    }
  
  /**
   * Fa le somme finali e ritorna la funzione
   *
   * @return array
   */
  public function totals():array {    
    
    $totals = $this->totals;

    if($this->type == 'b2c') {

        //Totale prezzo non scontato
        if(!empty($this->calculated['price_to_pay'])) {
            $totals['total_to_pay'] = $this->calculated['price_to_pay'] * $this->quantity;
        }

         //Tasse totali le ricalcolo facendo conto inverso da totale sennò non tornano arrotondamenti 
        if(!empty($this->calculated['tax']) && !empty($totals['total_to_pay'])) {
            //Scorporo iva
            $totals['total_taxes'] = $totals['total_to_pay'] - Number::format($totals['total_to_pay'] / (1 + (($this->calculated['tax'] / 100))));
        } else {
            $totals['total_taxes'] = 0;
        }

        //Prezzo finale
        $totals['total_price_taxes_excluded'] = $totals['total_to_pay'] - $totals['total_taxes'];

        if(!empty($this->calculated['discounts_total_percentage'])) { 

            //$totals['total_price_final'] = Number::addPercentage((float)$totals['total_to_pay'],(float)$this->calculated['discounts_total_percentage']);
            $totals['total_price_final'] = $this->calculated['price_final'] * $this->quantity;


            if(!empty($this->calculated['tax']) && !empty($totals['total_to_pay'])) {

                //Scorporo iva
                $totals['total_price_final_taxes'] = $totals['total_price_final'] - Number::format($totals['total_price_final'] / (1 + (($this->calculated['tax'] / 100))));
 
            } else {
                $totals['total_price_final_taxes'] = 0;
            }

            $totals['total_discount'] = $totals['total_price_final'] - $totals['total_to_pay'];

            $totals['total_price_final_taxes_excluded'] = $totals['total_price_final'] - $totals['total_price_final_taxes'];

        } else {

            //Senza sconto i totali sono uguali ai final
            $totals['total_price_final_taxes_excluded'] = $totals['total_price_taxes_excluded'];

            $totals['total_price_final_taxes'] = $totals['total_taxes'];

            $totals['total_price_final'] = $totals['total_to_pay'];

        }

    } else {

        //Totale prezzo non scontato
        if(!empty($this->calculated['price_taxes_excluded'])) {
            $totals['total_price_taxes_excluded'] = $this->calculated['price_taxes_excluded'] * $this->quantity;
        }

        //Tasse totali le ricalcolo facendo conto inverso da totale sennò non tornano arrotondamenti 
        if(!empty($this->calculated['tax']) && !empty($totals['total_price_taxes_excluded'])) {
            $totals['total_taxes'] = Number::percentage((float)$totals['total_price_taxes_excluded'],(float)$this->calculated['tax']);
        } else {
            $totals['total_taxes'] = 0;
        }

        //Prezzo finale
        $totals['total_to_pay'] = $totals['total_price_taxes_excluded'] + $totals['total_taxes'];

        if(!empty($this->calculated['discounts_total_percentage'])) { 

            //$totals['total_price_final_taxes_excluded'] = Number::addPercentage((float)$totals['total_price_taxes_excluded'],(float)$this->calculated['discounts_total_percentage']);
            $totals['total_price_final_taxes_excluded'] = $this->calculated['price_final_taxes_excluded'] * $this->quantity;

            if(!empty($this->calculated['tax']) && !empty($totals['total_price_taxes_excluded'])) {
                $totals['total_price_final_taxes'] = Number::percentage((float)$totals['total_price_final_taxes_excluded'],(float)$this->calculated['tax']);
            } else {
                $totals['total_price_final_taxes'] = 0;
            }

            $totals['total_discount'] = $totals['total_price_final_taxes_excluded'] - $totals['total_price_taxes_excluded'];

            $totals['total_price_final'] = $totals['total_price_final_taxes_excluded'] + $totals['total_price_final_taxes'];

        } else {

            //Senza sconto i totali sono uguali ai final
            $totals['total_price_final_taxes'] = $totals['total_taxes'];

            $totals['total_price_final_taxes_excluded'] = $totals['total_price_taxes_excluded'];

            $totals['total_price_final'] = $totals['total_to_pay'];

        }

    }


    //I VALORI INTERMEDI VENGONO SEMPLICEMENTE MOLTIPLICATI
    
    //Totale prezzo di acquisto



    //Totale prezzo venditore 
    if(!empty($this->calculated['price_vendor'])) {
        $totals['total_price_vendor'] = $this->calculated['price_vendor'] * $this->quantity;
    }

    //Totale commissioni pagamento 
    if(!empty($this->calculated['price_payment_commission'])) {
        $totals['total_payment_commission'] = $this->calculated['price_payment_commission'] * $this->quantity;
    }

    //Totali fee, ricarichi e commissioni
    if(!empty($this->calculated['price_agent_commission'])) {
        $totals['total_agent_commission'] = $this->calculated['price_agent_commission'] * $this->quantity;
    }

    if(!empty($this->calculated['price_reseller_fee'])) {
        $totals['total_reseller_fee'] = $this->calculated['price_reseller_fee'] * $this->quantity;
    }


    if(!empty($this->calculated['price_reseller_recharge'])) {
        $totals['total_reseller_recharge'] = $this->calculated['price_reseller_recharge'] * $this->quantity;
    }

    if(!empty($this->calculated['price_reseller_marketing'])) {
        $totals['total_reseller_marketing'] = $this->calculated['price_reseller_marketing'] * $this->quantity;
    }

    if(!empty($this->calculated['price_reseller_shipping_adjustment'])) {
        $totals['total_reseller_shipping_adjustment'] = $this->calculated['price_reseller_shipping_adjustment'] * $this->quantity;
    }

    if(!empty($this->calculated['price_recharge'])) {
        $totals['total_recharge'] = $this->calculated['price_recharge'] * $this->quantity;
    }

    if(!empty($this->calculated['price_store_recharge'])) {
        $totals['total_store_recharge'] = $this->calculated['price_store_recharge'] * $this->quantity;
    }

    if(!empty($this->calculated['price_fee'])) {
        $totals['total_fee'] = $this->calculated['price_fee'] * $this->quantity;
    }


    $values = array_merge($this->calculated,$totals);

    //Sort by key to better reading response 
    ksort($values);

    $values['currency'] = Price::$currency;

    $values['currency_conversion_rate'] = Price::$conversionRate;

    return $values;
   
  }

  /**
   * Calcola sconti su valori prodotto
   *
   * @return void
   */
  public function discounts() {

    //Sconto sul prodotto ha priorità su tutto
    if(!empty($this->calculated['discounts_product_percentage'])) {
  
        $this->calculated['discounts_total_percentage'] += $this->calculated['discounts_product_percentage'];
  
    }  

    //Sconto globale su tutti i prodotti, non cumulabile con sconto su prodotto singolo
    if(empty($this->calculated['price_discount']) && !empty($this->calculated['discounts_global_percentage'])) {

        $this->calculated['discounts_total_percentage'] += $this->calculated['discounts_global_percentage'];

    }

    //Lo sconto carrello può essere cumulabile o non cumulabile, se c'è sconto cumulabile anche se ho già settato uno sconto lo aggiungo
    /*
    COMMENTATO PERCHè ADESSO LO SCONTO CARRELLO è CALCOLATO SUL FINALE 
    if(!empty($this->discount) && ($this->discount_cumulable == true || empty($this->calculated['discounts_total_percentage']))) {

        $this->calculated['discounts_cart_percentage'] = $this->discount;

        $this->calculated['discounts_total_percentage'] += $this->discount;

    } else {

        $this->calculated['discounts_cart_percentage'] = 0;

    }
    */
    

    
    if($this->calculated['discounts_total_percentage'] > 100) {
        $this->calculated['discounts_total_percentage'] = 100;
    }

  }

  /**
   * Calcola commissioni agenti e fee resellers sui prezzi finali
   *
   * @return void
   */
  public function commissions() {

    //Il prezzo venditore è il prezzo finale (anche scontato) iva esclusa - il ricarico del reseller
    $this->calculated['price_vendor'] = $this->calculated['price_base'] - $this->calculated['price_payment_commission'];

    
    //Fee presa al reseller sul prezzo iva esclusa
    if(!empty($this->calculated['price_reseller_fee_percentage'])) {
        $this->calculated['price_reseller_fee'] = Number::percentage($this->calculated['price_taxes_excluded'],$this->calculated['price_reseller_fee_percentage']);
    }

    //Commissione data al reseller
    if(!empty($this->calculated['price_agent_commission_percentage'])) {
        $this->calculated['price_agent_commission'] = Number::percentage($this->calculated['price_taxes_excluded'],$this->calculated['price_agent_commission_percentage']);
    }

    //Calcolo commissioni agente
    if(!empty($this->commissions)) {

        foreach($this->commissions as $commission) {

            //check quantity first
            if(!Number::isBetween((int)$this->quantity, (int)$commission['quantity_min'],(int)$commission['quantity_max'])) {
              continue;
            }
      
            //then control price min
            if(!Number::isBetween((float)$this->calculated['price_taxes_excluded'], (float)$commission['price_min'],(float)$commission['price_max'])) {
              continue;
            }
      
            //if not blocked return the percentage
            $this->calculated['price_agent_commission_percentage'] = $commission['percentage'];

            $this->calculated['price_agent_commission'] = Number::percentage((float)$this->calculated['price_taxes_excluded'],(float)$commission['percentage']);

            break;
      
          }
      

    }

  }


  /**
   * Setta sconto globale a carrello e gli fa il cast a float
   *
   * @param integer|float $discount
   * @return object
   */
  public function setDiscount($discount, $cumulable = false):object {

    $this->discount = (float)$discount;

    $this->discount_cumulable = $cumulable;

    if($discount > 100) {
      $this->discount = 100.00;
    }

    if($discount < 0) {
        $this->discount = 0;
    }

    return $this;
    
  }

  
   /**
   * Setta e controlla un client type
   *
   * @param string $clientType
   * @return object
   */
  public function setClientType(string $clientType): object {

    $this->type = $clientType;

    return $this;
  }




  /**
   * Setta id agente e recupera le commissioni, se nullo resetta commissioni
   *
   * @param integer|null $id_agents
   * @return object
   */
  public function setAgent(int|null $id_agents): object {

    //Get reseller commissions by quantity, check again with price on query
    if(is_null($id_agents) || !$this->commissions = Db::getArray("SELECT * FROM agents_commissions WHERE (FIND_IN_SET(".encode($this->type).",type) OR type IS NULL) AND id_agents = ".$id_agents. " ORDER BY quantity_min ASC")) {
        $this->commissions = array();
    }
  

    return $this;
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