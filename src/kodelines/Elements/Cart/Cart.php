<?php

declare(strict_types=1);

namespace Elements\Cart;

use Kodelines\Db;
use Elements\Orders\Orders;
use Elements\Tracking\Tracking;

class Cart {

  /**
   * Queste configurazioni rispecchiano quelle su cart.js che vengono passate da array setup e settate nel construct
   *
   */
  public $id_users = null;

  public $id_resellers = null;

  public $id_agents = null;

  public $id_countries = null;

  public $id_stores = null;

  public $type = 'b2c';

  public $discount_percentage = null;

  public $discount_cumulable = null;

  public $order = [];



  /**
   * TODO: questo è qui perchè funziona anche per front end, 
   * quando gestito solo da api agganciare cart middleware a tutte le chiamate store e configuratore e risistemare questo casino
   */
  public function __construct()
  {
  
    //Questi valori vengono passati da header e definiti da AuthMiddleware o JwtAuthentication
    if(defined('_CLIENT_TYPE_')) {
      $this->type = _CLIENT_TYPE_;
    }

    if(defined('_ID_COUNTRIES_')) {
      $this->id_countries = _ID_COUNTRIES_;
    }

    if(defined('_ID_AGENTS_')) {
      $this->id_agents = _ID_AGENTS_;
    }

    if(defined('_ID_RESELLERS_')) {     
      $this->id_resellers = _ID_RESELLERS_;
    }

    if(defined('_ID_STORES_')) {     
      $this->id_stores = _ID_STORES_;
    }

  }


  /**
   * Controlla se c'è un carrello in sessione, se non c'è lo crea
   *
   * @return array|false
   */
  public function check($id = false):void { 

    $filters = [];

    //Id restringe get a quell'ordine
    if($id) {
      $filters = ['id' => $id];
    } 
    
    //Utente e token sempre settati
    if(user()) {

      $this->id_users = user('id');

      $filters = array_merge($filters,['oauth_tokens_jti' => null, 'id_users' => user('id')]);

    } elseif(defined('_OAUTH_TOKEN_JTI_')) {

      $filters = array_merge($filters,['oauth_tokens_jti' => _OAUTH_TOKEN_JTI_, 'id_users' => null]);

    }  

    if(empty($filters)) {
      return;
    }
   
    //Setto filtri predefiniti per tutti i carrelli con variabili globali
    $filters = array_merge($filters,[
      'status' => 'cart|pending',
      'id_resellers' => $this->id_resellers,
      'id_agents' => $this->id_agents,
      'id_stores' => $this->id_stores,
      'orderby' => 'id DESC',
      'limit' => 1
    ]);

    //Se è stringa è jti, altrimenti è id
    if($order = Db::getRow(Orders::query($filters))) { 
      
      if($this->isAllowed($order)) { 
        $this->order = $order;
      } 
  
    }

  }

  /**
   * Crea nuovo carrello
   */
  public function create():array {

    $setup = [
      'discount_percentage' => $this->discount_percentage,
      'discount_cumulable' => $this->discount_cumulable,
      'type' => $this->type,
      'id_resellers' => $this->id_resellers,
      'id_agents' => $this->id_agents,
      'id_countries' => $this->id_countries,
      'id_stores' => $this->id_stores,
      'id_tracking' => Tracking::getCurrentId()
    ];

    if(user()) {
      $setup['id_users'] = user('id');
    } else {
      $setup['oauth_tokens_jti'] = _OAUTH_TOKEN_JTI_;
    }

    $this->order = Orders::create($setup);


    return $this->order;

  }


  /**
   * Controlla se un carrello è permesso
   *
   * @param array $cart
   * @return boolean
   */
  public function isAllowed(array $order):bool {

    //TODO: questo basterebbe controllarlo con === e fare cast ma cosi per ora meglio per debug

    //Controllo roba uguale

    if(!empty($this->id_users) && !empty($order['id_users']) && $order['id_users'] <> $this->id_users) {
      return false;
    }

    if(!empty($this->id_resellers) && !empty($order['id_resellers']) && $order['id_resellers'] <> $this->id_resellers) {
      return false;
    }

    if(!empty($this->id_agents) && !empty($order['id_agents']) && $order['id_agents'] <> $this->id_agents) {
      return false;
    }

    if(!empty($this->id_stores) && !empty($order['id_stores']) && $order['id_stores'] <> $this->id_stores) {
      return false;
    }

    //Ordine id utente vuoto e utente loggato lo assegno e lo refresho
    if(empty($order['id_users']) && !empty($this->id_users) && !empty($order['id'])) {

      $this->assign($order['id'],$this->id_users);

      $order = Orders::refresh($order,true);

    }

    //Controllo roba settata
    if(empty($this->id_users) && !empty($order['id_users'])) {
      return false;
    }

    /*
    if((empty($this->id_resellers) && !empty($order['id_resellers'])) || (empty($order['id_resellers']) && !empty($this->id_resellers))) {
      return false;
    }

    if((empty($this->id_agents) && !empty($order['id_agents'])) || (empty($order['id_agents']) && !empty($this->id_agents))) {

      //TODO: Qua va assegnato?

      return false;
    }
    */

    return true;
  }



  /**
   * Associa un carrello ad un utente 
   *
   * @param integer $id_store_orders
   * @param integer $id_users
   * @return boolean
   */
  public function assign(int $id_store_orders,int $id_users):bool {
    return Db::query("UPDATE store_orders SET id_users = " . id($id_users) . " WHERE id = " . id($id_store_orders));
  }



  /**
   * Resetta carrello per token
   */
  public static function reset(array $order):bool {

    return Db::query("UPDATE store_orders SET oauth_tokens_jti = NULL WHERE id = " . id($order['id']));

  }

}

?>