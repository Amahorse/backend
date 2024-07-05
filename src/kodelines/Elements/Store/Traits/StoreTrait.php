<?php

namespace Elements\Store\Traits;

use Kodelines\Exception\RuntimeException;
use Elements\Store\Helpers\Price;
use Elements\Store\Warehouse;
use Elements\Shipping\Shipping;

trait StoreTrait {

    /**
     * Contiene array di valori dei settaggi del database
     *
     * @var array
     */
    public $settings = [];

    public Price $price;

    public $type = 'b2c';

    public $id_store_tax = null;
  
    public $id_countries = null;
  
    public $id_agents = null;
  
    public $id_resellers = null;
  
    public $id_stores = null;
  
    public $id_store_orders = null;
  
    public $language;
  
    public $quantity = 1;

    public function init() {

        $this->price = new Price;
    
        $this->language = language();
    
        //Queste costanti sono definite da ApiMiddleware o ViewMiddleware in base a sessioni, utenti o token, 
        //altrimenti prende quelli di default, per avere store con valori personalizzati creare nuova istanza 
    
        if(defined('_ID_STORES_')) {
          $this->setStore(_ID_STORES_);
        } 
    
        if(defined('_CLIENT_TYPE_')) {
          $this->setClientType(_CLIENT_TYPE_);
        } 
    
        //La nazione può essere forzata, se non forzata setto nazione di default
        if(defined('_ID_COUNTRIES_')) { 
          $this->setCountry(_ID_COUNTRIES_);
        } else {
          $this->setCountry((int)config('default','id_countries'));
        }
    
        
        if(defined('_ID_RESELLERS_')) { 
          $this->setReseller(_ID_RESELLERS_);
        }
    
        if(defined('_ID_AGENTS_')) {
          $this->setAgent(_ID_AGENTS_);
        }
    
        //Questo viene definito da constructor istanza carrello controllando cookie,headsrs e cose varie , quindi se si vuole fare i controlli se prodotti in carrello prima di chiamare store va fatto Cart::getInstance();
        if(defined('_ID_STORE_ORDERS_')) {
          $this->id_store_orders = _ID_STORE_ORDERS_;
        }
    
    
    }


      /**
   * Setta e controlla un client type
   *
   * @param string $clientType
   * @return object
   */
  public function setClientType(string $clientType): object {

    if($clientType !== 'b2b' && $clientType !== 'b2c' && $clientType !== 'horeca') {
      throw new RuntimeException('Client type "' .$clientType . '" Not valid');
    }

    $this->type = $clientType;

    //Assegno client type anche all'istanza prezzi
    $this->price->setClientType($this->type);
 
    return $this;
  }

  /**
   * Setta nuovo id nazione 
   *
   * @param integer $id_countries
   * @return object
   */
  public function setCountry(int $id_countries):object {

    $this->id_countries = $id_countries;

    return $this;
  }

  /**
   * Setta nuovo agente
   *
   * @param integer $id_agents
   * @return object
   */
  public function setAgent(int $id_agents):object {

    $this->id_agents = $id_agents;

    //Assegno l'agente anche all'istanza prezzi cosi recupera le commissioni
    $this->price->setAgent($id_agents);

    return $this;
  }

  /**
   * Setta nuovo reseller
   *
   * @param integer $id_resellers
   * @return object
   */
  public function setReseller(int $id_resellers):object {

    $this->id_resellers = $id_resellers;

    return $this;
  }





  /**
   * Setta id tassazione
   *
   * @param integer $id
   * @return object
   */
  public function setTax(int $id):object {

    $this->id_store_tax = $id;

    return $this;

  }

  /**
   * Setta ordine
   *
   * @param integer $id_store_orders
   * @return object
   */
  public function setOrder(int $id_store_orders):object {

    $this->id_store_orders = $id_store_orders;

    return $this;
  }


  /**
   * Setta quantità
   *
   * @param integer $quantity
   * @return object
   */
  public function setQuantity(int $quantity):object {

    $this->quantity = $quantity;

    return $this;
  }

  /**
   * Setta Store
   *
   * @param integer $id_stores
   * @return object
   */
  public function setStore(int $id_stores):object {

    $this->id_stores = $id_stores;


    return $this;
  }
 
 
    
  /**
   * Genera campi base per tutti i prodotti
   *
   * @param integer $id_stores
   * @return array
   */
  public function generateBaseFields(array $product) {


        //Calcolo il peso totale del prodotto
        if(!empty($product['weight'])) {
          $product['total_weight'] = $product['weight'] * $product['quantity'];
        } else {
          $product['total_weight'] = NULL;
        }
    
        //Calcolo in quale disponiblità va il prodotto e quanti ce ne sono disponibili
        $product['availability'] = Warehouse::getAvailabilityType($product);
    
        $product['availability_max'] = Warehouse::getAvailabilityMax($product);
    
        //Calcolo data di spedizione 
        if(empty($product['shipping_delay'])) {
          $product['shipping_delay'] = 0;
        }
        
        //Allo shipping delay di base che in query viene preso dallo store o dai config si aggiunge il timing_supply se il prodotto è in disponibilità virtuale
        if($product['availability'] == 'virtual' && !empty($product['timing_supply'])) {
          $product['shipping_delay'] = (int)$product['shipping_delay'] + (int)$product['timing_supply'];
        } 
        
        //TODO: quando c'è join con corrieri e zip code i giorni di spedizione prenderli da li
    
        if(!empty($product['shipping_max_hour'])) {
    
          $product['date_retire'] = Shipping::calcRetire((int)$product['shipping_delay'],$product['shipping_max_hour'],"'monday','tuesday','wednesday','thursday','friday'");
    
        } else {
    
          $product['date_retire'] = Shipping::calcRetire((int)$product['shipping_delay'],config('store','shipping_max_hour'),"'monday','tuesday','wednesday','thursday','friday'");
    
        }
    

    if(!empty($product['type_data']) && !is_array($product['type_data'])) {

      $type_data = json_decode($product['type_data'],true);
  
      if(is_array($type_data)) {
          $product['type_data'] = $type_data;
      }
  
    } else {
        $product['type_data'] = array();
    }

    //Se il minimo ordine non è raggiunto, setto la quantità forzata al minimo ordine 
    if(!empty($product['minimum_order']) && $product['minimum_order'] > $product['quantity'] && !auth('administrator',true)) {
      $product['quantity'] = $product['minimum_order'];
    }
        
    //Setto nome client type per capire che tipo è
    $product['client_type'] = $this->type;


  return $product;
  }
 
 

}

?>