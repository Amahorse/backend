<?php

declare(strict_types=1);

namespace Elements\Orders;

use Kodelines\Db;
use Kodelines\Oauth\Scope;
use Kodelines\Mailer;
use Kodelines\Settings;
use Kodelines\Abstract\Decorator;
use Kodelines\Exception\ValidatorException;
use Elements\Store\Discounts;
use Elements\Store\Warehouse as StoreWarehouse;
use Elements\Configurator\Warehouse as ConfiguratorWarehouse;
use Elements\Store\Tax;
use Elements\Store\Warehouse;
use Elements\Shipping\Rules;
use Elements\Shipping\Shipping;
use Elements\Invoices\Invoices;
use Elements\Invoices\Helpers\Pdf;
use Elements\Invoices\Helpers\Xsd;
use Elements\Data\History;
use Elements\Data\Data;
use Elements\Users\Users;
use Kodelines\Helpers\Price;
use Kodelines\Tools\Number;


class Orders extends Decorator  {


 /**
  * Aggiorna i totali dell'ordine, se ricalcolate è a true, riprende tutti i prodotti e aggiorna le informazioni
  *
  * @param integer $id_store_orders
  * @param boolean $recalculate
  * @param boolean $dry   Se in modalità dry non aggiorna database
  * @return array
  */
 public static function refresh(array $order, $recalculate = false, $dry = false):array {

    //Ordine senza id ordine nuovo
    if(empty($order['id'])) {
      return $order;
    }

    //Prevengo ricalcolo ordine se ordine è confermato
    if(!empty($order['status']) && ($order['status'] == 'confirmed' || $order['status'] == 'deleted' || $order['status'] == 'completed')) {
      return $order;
    }
  

    //Controllo codice sconto se è ancora valido, se non è più valido resetto l'ordine
    if(!empty($order['discount_code']) && !Discounts::checkCode($order['discount_code'])) {

      if(!$dry) {
        Discounts::reset($order);
      }

      //Forzo sempre ricalcolo se è cambiato il coupon
      $recalculate = true;
    }
    
    //Disabilito forzatamente ricalcolo ordine se è preventivo
    if(!empty($order['status']) && ($order['status'] == 'quote')) {
      $recalculate = false;
    }

    $products = Products::list(['id_store_orders' => $order['id']]);

    if($recalculate == true) {

        $recalculated = [];

        foreach($products as $recalc) {

            //Faccio un catch del validator exception perchè qui non deve tornare errore validatore ma cancellare il prodotto se non trovato
            try {
   
                //Ricalcolo i valori del prodotto
                $new = Products::generate($recalc,$order,$recalc['add_price']);

                if(!$dry) {
                  $recalculated[] = Products::update($recalc['id'],$new);
                } else {
                  $recalculated[] = $new;
                }
      

            } catch (ValidatorException) {

                //Se non lo trova più elimina il prodotto
                if(!$dry) {
                  Products::delete($recalc['id']);
                }

            }

        }

        $products = $recalculated;
    }
    
    $refreshed = self::calcSum($products,$order);

    if(!$dry) {

      if(!$order = self::update($order['id'],$refreshed)) {
        throw new ValidatorException('cart_error');
      }

      //Prima elimino vecchie tasse in caso ci fossero prodotti tolti
      if(!Db::delete('store_orders_vats','id_store_orders',$order['id'])) {
        throw new ValidatorException('database_error');
      }

      //Poi le reinserisco da capo  
      if(!Db::insertMultiple($refreshed['vats'],'store_orders_vats','id_store_orders',$order['id'])) {
        throw new ValidatorException('database_error');
      }
    
    } else {

      $order = array_merge($order,$refreshed);

    }


    return $order;
 }

  /**
   * Calcola somme dei prodotti
   *
   * @param array $products
   * @param array  $order 
   * @return array
   */
  public static function calcSum(array $products, array $order):array {



    $values = array(
        "total_price" => 0.00,
        "total_price_taxes_excluded" =>  0.00,
        "total_price_final" => 0.00,
        "total_price_final_taxes_excluded" => 0.00,
        "total_price_final_taxes" => 0.00,
        "total_price_vendor" => 0.00,
        "total_reseller_fee" => 0.00,
        "total_reseller_recharge" => 0.00,
        "total_reseller_marketing" => 0.00,
        "total_reseller_shipping_adjustment" => 0.00,
        "total_agent_commission" => 0.00,
        "total_taxes" => 0.00,
        "total_to_pay" => 0.00,
        "total_fee" => 0.00,
        "total_discount" => 0.00,
        "total_discount_products" => 0.00,
        "total_recharge" => 0.00,
        "total_quantity" => 0,
        "total_products" => count($products),
        "total_products_shipping" => 0,
        "total_weight" => 0,
        "availability_warehouse" => 0,
        "availability_virtual" => 0,
        'payable' => 1,
        'vats' => []
    );

    $vats = [];

    foreach($products as $product) {

      //Aggiungo peso
      if(!empty($product['total_weight'])) {
        $values['total_weight'] += $product['total_weight'];
      }
    
      //Splitto disponibilità
      if(!empty($product['availability']) && $product['availability'] == 'virtual') {

        $values['availability_virtual'] +=  $product['quantity'];

      } else {

        $values['availability_warehouse'] +=  $product['quantity'];

      }

      //Genero disponibilità massima per controllo se non settata
      if(!isset($product['availability_max'])) {
        $product['availability_max'] = Warehouse::getAvailabilityMax($product);
      }

      if($product['availability_max'] < $product['quantity'] || $product['status'] <> 'on_sale') { 
        $values['payable'] = 0;
      }
      
      //Il prodotto conta ai fini calcolo quantità spedizione
      if(!empty($product['count_on_shipping'])) { 
        $values['total_products_shipping'] += $product['quantity'];
      }

      //Se il prodotto è un componente di un pacchetto salto dai totali perchè il prezzo viene considerato nel prodotto principale
      if(!empty($product['id_store_orders_products_parent']) && $product['fixed_component'] == 1) {
        continue;
      }

      //Se l'ordine ha final tax (tassa globale su ordine usa), l'unica aliquota è quella
      if(isset($order['final_tax']) && !empty((float)$order['final_tax'])) {
        $product['tax'] = $order['final_tax'];
      }

      //Creo un array per ogni aliquota
      if(!isset($vats[$product['tax']])) {

        $vats[$product['tax']] = array(
          "total_price_final" => 0,
          "total_price_final_taxes_excluded" => 0,
          "total_price_final_taxes" => 0,
          "total_taxes" => 0,
          "total_to_pay" => 0,
          "total_price_taxes_excluded" => 0,
          "total_discount" => 0,
          "id_store_tax" => $product['id_store_tax']
        );

      }

      //Aggiungo prezzi finali ad array singole aliquote, i valori sono sempre i finali scontati del prodotto
      $vats[$product['tax']]['total_price_final'] += $product['total_to_pay']; //Totale valore sconti esclusi
      $vats[$product['tax']]['total_price_final_taxes_excluded'] += $product['total_price_taxes_excluded']; //Totale valore sconti esclusi
      $vats[$product['tax']]['total_price_final_taxes'] += $product['total_taxes']; //Totale valore sconti esclusi

      $vats[$product['tax']]['total_to_pay'] += $product['total_to_pay']; //Totale valore sconti esclusi
      $vats[$product['tax']]['total_price_taxes_excluded'] += $product['total_price_taxes_excluded']; //Totale valore sconti esclusi
      $vats[$product['tax']]['total_taxes'] += $product['total_taxes']; //Totale valore sconti esclusi

      //Aggiungo prezzi finali ad array singole aliquote

      //Totale sconto applicato ai singoli prodotti indipendentemente se cumulabile o meno (Serve solo per avere importo sconto totale ordine su report)
      $values['total_discount_products'] += $product['total_discount']; 
      $values['total_price_vendor'] += $product['total_price_vendor'];
      $values['total_recharge'] += $product['total_recharge']; //Totale ricarico
      $values['total_fee'] += $product['total_fee']; //Fee Totale
      $values['total_quantity'] += $product['quantity'];
      $values['total_agent_commission'] += $product['total_agent_commission'];
      $values['total_reseller_fee'] += $product['total_reseller_fee'];
      $values['total_reseller_recharge'] += $product['total_reseller_recharge'];
      $values['total_reseller_marketing'] += $product['total_reseller_marketing'];
      $values['total_reseller_shipping_adjustment'] += $product['total_reseller_shipping_adjustment'];



    }


    //Faccio ciclo delle tasse per applicare sconti su aliquote e trovare i totali reali
    foreach($vats as $aliquota => $vat) {

      //Questi servono per avere array pronto per il database
      $vat['tax'] = (float)$aliquota;

      $vat['id_store_orders'] = $order['id'];

      $vat['discount_percentage'] = (float)$order['discount_percentage'];

      $vat['total_price_final'] = Number::addPercentage((float)$vat['total_price_final_taxes_excluded'],$vat['tax']);

      $vat['total_price_final_taxes'] = $vat['total_price_final'] - $vat['total_price_final_taxes_excluded'];

      $vat['total_price_taxes_excluded'] = $vat['total_price_final_taxes_excluded'];


      //In caso di sconto ricalcolo le tasse
      if(!empty($vat['discount_percentage'])) {


        //Applico sconti su aliquote in modo diverso tra b2c e b2b e calcolo tasse
      if($order['type'] == 'b2c') {

        $vat['total_to_pay'] = Number::removePercentage((float)$vat['total_price_final'], $vat['discount_percentage']);

        $vat['total_discount'] = $vat['total_price_final'] - $vat['total_to_pay'];

        $vat['total_price_taxes_excluded'] = Number::removePercentage((float)$vat['total_to_pay'],$vat['tax']);

        $vat['total_taxes'] = $vat['total_to_pay'] - $vat['total_price_taxes_excluded'];  
  
      } else {

        $vat['total_price_taxes_excluded'] = Number::removePercentage((float)$vat['total_price_final_taxes_excluded'], $vat['discount_percentage']);

        $vat['total_discount'] = $vat['total_price_final_taxes_excluded'] - $vat['total_price_taxes_excluded'];

        $vat['total_to_pay'] = Number::addPercentage((float)$vat['total_price_taxes_excluded'],$vat['tax']);

        $vat['total_taxes'] = $vat['total_to_pay'] - $vat['total_price_taxes_excluded'];

      }

  
  
    }  else {


      $vat['total_taxes'] = $vat['total_price_final_taxes'];

      $vat['total_to_pay'] = $vat['total_price_final'];

      $vat['total_discount'] = 0.00;


    }

    $vat = Price::formatMultiple($vat);

    $values['total_discount'] += $vat['total_discount'];
    
    $values['total_price_final'] += $vat['total_price_final_taxes_excluded'];

    $values['total_price_final'] += $vat['total_price_final_taxes'];

    $values['total_price_final_taxes_excluded'] += $vat['total_price_final_taxes_excluded'];

    $values['total_taxes'] += $vat['total_taxes'];

    $values['total_price_taxes_excluded'] += $vat['total_price_taxes_excluded'];

    //Total to pay somma prezzi iva esclusa e tasse
    $values['total_to_pay'] += $vat['total_price_taxes_excluded'];

    $values['total_to_pay'] += $vat['total_taxes'];

    $values['vats'][] = $vat;

    }

    //Prezzo totale è sempre tutto incluso, totale da pagare può avere sconti in euro applicati
    $values['total_price'] = $values['total_to_pay'];

    //Fix valore sconto per b2c
    if($order['type'] == 'b2c') {
      $values['total_discount'] = $values['total_price_final'] - $values['total_to_pay'];
    }


    //Applico sconto in euro
    if(!empty($order['discount_price']) && !empty($order['total_to_pay'])) {

      $values['total_to_pay'] = $values['total_to_pay'] - $order['discount_price'];

      if($values['total_to_pay'] < 0) {
        $values['total_to_pay'] = 0.00;
      }

    }

    return $values;
  }



  /**
   * Crea spedizione per prodotti dentro a ordine, se non specificati prodotti crea spedizione per tutti i prodotti dentro all'ordine
   *
   * @param array $shipping
   * @param array $order
   * @param array $products
   * @return array|boolean
   */
  public static function createShipping(array $shipping, array $order, $products = []):array|bool {

    //Ordine offline non contiene spedizioni
    if(!empty($order['offline'])) {
      //TODO: da gestire
    }

    //Prendo dati spedizione in base alla priorità, id_data_history o id_data possono essere già definiti da listino
    if(!empty($shipping['id_data_history'])) {

      $data = History::get($shipping['id_data_history']);

    } elseif(!empty($shipping['id_data'])) {

      $data = History::get($shipping['id_data']);

    } else {

      //Sono custom dentro a shipping
      if(!$data = Data::validate($shipping)) {
        throw new ValidatorException('shipping_data_error');
      }

    }

    if(isset($shipping['id_shipping_rules'])) {

      if($shipping['id_shipping_rules'] == 'custom' || empty($shipping['id_shipping_rules'])) {

        $price = [];

        $shipping['id_shipping_rules'] = NULL;

        if(!isset($shipping['price_taxes_excluded'])) {
          $price['price_taxes_excluded'] = 0.00;
        } else {
          $price['price_taxes_excluded'] = $shipping['price_taxes_excluded'];
        }


        if(!empty($shipping['id_store_tax']) && $taxes = Tax::get(id($shipping['id_store_tax']))) {
          $price['tax'] = $taxes['tax'];
        }
        
        $rule = Rules::price($price,$order);
        
        $rule = array_merge($shipping,$rule);

      } else {
        
        if(!$rule = Rules::calculate(id($shipping['id_shipping_rules']),$data,$order)) { 
          throw new ValidatorException('shipping_rule_error');
        }

      }
    
      //Faccio unset sennò sul merge sovrascrive id 
      if(!empty($rule['id'])) {
        unset($rule['id']);
      }

      $shipping = array_merge($shipping,$rule);

    }

    if($order['id_countries'] <> $data['id_countries']) {

      $order['id_countries'] = $data['id_countries'];

      $refresh = true;
    }

    if(!empty($shipping['calculated_by']) && ($shipping['calculated_by'] == 'usa' || $shipping['calculated_by'] == 'export')) {
      $refresh = true;
    }

    if(empty($products)) {
      //Ordino prima i prodotti con spedizione già settata almeno la prendo e faccio update e poi la uso per gli altri prodotti
      //NOTE: appena si fa la funzione di split vanno ordinati i prodotti per id_shipping
      $products = Products::list(['id_store_orders' => $order['id'], 'orderby' => 'id_shipping DESC']);
    }

    $total_weight = 0;

    foreach($products as $product) {

      $total_weight += $product['total_weight'];

      if(!empty($product['id_shipping'])) {
  
        //Faccio semplicemente update della vecchia spedizione trovata 
        $real = Shipping::update(id($product['id_shipping']),$shipping);

      } else {

        //Se non settata creo spedizione nuova 
        if(!isset($real)) {
          $real = Shipping::create($shipping);
        }

      }

      $pu = "id_shipping = " . id($real['id']);

      if(!empty($refresh)) {
        $pu .= ", id_store_tax = NULL";
      }

      Db::query("UPDATE store_orders_products SET " . $pu . " WHERE id = " . id($product['id']));

    }

    if(isset($real)) {
      Db::query("UPDATE shipping SET weight = " . Db::encode($total_weight) . " WHERE id = " . id($real['id']));
    }

    $total_shipping = 0.00;

    $total_excise = 0.00;

    $total_duties = 0.00;

    //Sommo tutte le spedzioni e aggiorno ordine
    foreach(Shipping::list(["id_store_orders" => $order["id"]]) as $shipping) {

      $total_shipping += $shipping['price_to_pay'];

      $total_excise += $shipping['price_excise'];

      $total_duties += $shipping['price_duties'];
    }

        //Aggiorno sempre totale spedizione
    $update = [
      'total_shipping' => Price::format($total_shipping),
      'total_excise' => Price::format($total_excise),
      'total_duties' => Price::format($total_duties),
    ];

    //Se nazione di spedizione è diversa da quella dello store ricalcolo l'ordine
    if(!empty($refresh)) {
  
      $update['id_countries'] = $shipping['id_countries'];

      if(!empty($rule['final_tax'])) {
        $update['final_tax'] = Price::format($rule['final_tax']);
      } else {
        $update['final_tax'] = Price::format(0.00);
      }

      $refresh = true;

    } 
  
    
    Db::updateArray('store_orders',$update,'id',$order['id']);
   
    if(!empty($refresh)) {
      
      //Refresho l'ordine con i valori aggiornati se cambiati i valori nazione e tasse
      $order = self::refresh(self::get($order['id']),true);
    }
    
    return $shipping;
  }

  /**
   * Ritorna selezione stati ordine in base ai permessi utente
   *
   * @return array
   */
  public static function statusList(array $order):array {

    $list = Db::enumOptions('store_orders','status');

    if(Scope::is('agent') || $order['status'] == 'confirmed') {

      unset($list['pending']);

      unset($list['cart']);

    }

    if($order['status'] == 'confirmed') {
      unset($list['quote']);
    }

    return $list;

  }


  /**
   * Conferma oridine
   *
   * @param integer $id
   * @param boolean $send_email
   * @return bool
   */
  public static function confirm(int $id, $send_email = false) {



      if(!$order = self::get($id)) {
        return false;
      }

      if($order['status'] == 'confirmed') {
        return false;
      }

      //Get last order number
      if(!$order['number'] = self::getNumber()) {
        return false;
      }

      $values = array('number' => $order['number'], 'date_order' => _NOW_, 'status' => 'confirmed');


        //Aggiorno notifica se non send mail
        if(!$send_email) {
          $values['notified'] = 1;
        }

        //if discount code is not null assign to the user
        if(!empty($order['discount_code']) && $discount = Discounts::getFromCode($order['discount_code'])) {

          Db::replace('store_discounts_users',array('id_users' => $order['id_users'], 'id_store_discounts' => $discount['id']));

          //associate order to reseller
          //TODO: controllare in qualche modo se cupon viene da agente e se uno lo cambia o lo resetta deve resettarsi anche questo
          if(!empty($discount['id_agents'])) {
            $values['id_agents'] = $discount['id_agents'];
          }


        }

      //Aggiorno le spedizioni a confermate con date spedizione a partire da oggi
      foreach(Shipping::list(['id_store_orders' => $id]) as $shipping) {

        $update = [];

        $update['processing'] = 'on_processing';

        //TODO: questo può generare errore se uno conferma ordine e non salva prima modifica spedizione
        if(!empty($shipping['retire_day']) && !empty($shipping['delivery_day'])) {

          $delay = (int)Shipping::getShippingMaxDelay($shipping['id']) + (int)$order['shipping_delay'];

          // 1) calcolo data ritiro
          $update['date_retire'] = Shipping::calcRetire($delay,$order['shipping_max_hour'],$shipping['retire_day']);

          // 2) calcolo data minima di consegna stimata in base a data ritiro tempo minimo di consegna
          $update['date_delivery'] = Shipping::calcDelivery($shipping['delivery_day'], (int)$shipping['min_timing_delivery'],  $update['date_retire']);

          // 2) calcolo data massima di consegna stimata in base a data ritiro tempo massimo di consegna
          $update['date_delivery_max'] = Shipping::calcDelivery($shipping['delivery_day'], (int)$shipping['max_timing_delivery'],  $update['date_retire']);
  
        } else {

          $update['date_retire'] = _NOW_;

          $update['date_delivery'] = _NOW_;

          $update['date_delivery_max'] = _NOW_;
  
  
        }
        
    
        Db::updateArray('shipping',$update,'id',$shipping['id']);
      }



      if(!Db::updateArray('store_orders',$values,'id',$id)) {
        return false;
      }


      //Remove products from
      self::removeProducts($id);


      return true;



  }


  /**
   * 
   * Cancella ordine e rimette i prodotti in disponibilità
   */
   public static function cancel(int $id) {

       if(!$order = self::get($id)) {
         throw new ValidatorException();
       }


       //Remove products from
       if($order['status'] == 'confirmed') {
         self::addProducts($id);
       }

       //update order status
       if(!Db::update('store_orders','status','deleted','id',$id)) {
         throw new ValidatorException('database_error');
       }
      
      //TODO: questo e conferma sono trigger database
      //Aggiorno le spedizioni a cancellate
      foreach(Shipping::list(['id_store_orders' => $id]) as $shipping) {

        if($shipping['processing'] !== 'sent' && $shipping['processing'] !== 'delivered') {
          Db::update('shipping','processing','canceled','id',$shipping['id']);
        }
        
      }

       /*
       TODO: fare email ordine annullato con opzione su pannello ordine

       if($send_email) {

         Mailer::queue('order-complete', $order['id_users'], $order,false,1);

         Mailer::queue('order-complete', 'info@bottle-up.com', $order,false,1);

       }
       */

       return true;


   }


  /**
   * Ritorna progessivo prossimo numero ordine 
   */
  public static function getNumber():int|bool {

    if(!$progressive = Db::getValue("SELECT CASE WHEN number IS NULL THEN 1 ELSE (MAX(number) + 1) END AS progressive FROM store_orders WHERE number IS NOT NULL")) {
      return false;
    }

    return (int)$progressive;
  }

  /**
   * Toglie i prodotti da magazzino per ordine confermato
   */
  public static function removeProducts(int $id) {

    $products = Products::list(['id_store_orders' => $id]);

    foreach($products as $product) {

      //Riprendo il valore da dove devo togliere la disponibilità in tempo reale 
      $availability = StoreWarehouse::getAvailabilityType($product);

      //Serve disponibilità alternativa per eventuale split
      if($availability == 'virtual') {
        $alt = 'warehouse';
      } else {
        $alt = 'virtual';
      }

      //Empty è disponibilità illimitata e continua diretta
      if(empty($product['availability_'.$availability]) && $product['availability_'.$availability] !== 0) {
        continue;
      }

      //Può succedere che la disponibilità virtuale sia inferiore a quella totale, in tal caso devo splittare
      if($product['availability_'.$availability] < $product['quantity']) {

          //Quantità da togliere nella disponibilità selezionata
          $split = $product['quantity'] - $product['availability_'.$availability];

          //Quantità originale - quantità nuova
          $quantity = $product['quantity'] - $split;

      } else {

          $quantity = $product['quantity'];

          $split = 0;
      }


      //Prodotto solo configuratore o store e connesso a configuratore con segui disponibilità
      if((!empty($product['id_configurator']) && empty($product['id_store_products'])) || (!empty($product['id_store_products']) && !empty($product['id_configurator']) && !empty((int)$product['rules_configurator_availability']))) {

        ConfiguratorWarehouse::updateAvailability($product['id_configurator'],'-',$availability,$quantity);

        if(!empty($split)) {
          ConfiguratorWarehouse::updateAvailability($product['id_configurator'],'-',$alt,$split);
        }

      } else {

        //Prodotto solo store o prodotto senza segui disponibilità
        StoreWarehouse::updateAvailability($product['id_store_products'],'-',$availability,$quantity);
        
        if(!empty($split)) {
          StoreWarehouse::updateAvailability($product['id_store_products'],'-',$alt,$split);
        }

      }

      //Faccio update di dove ho tolto la disponibilità al prodotto
      Db::update('store_orders_products','availability',$availability,'id',$product['id']);

      //Esporto JSON prodotti per backup a ordine confermato
      file_put_contents(uploads() . 'exports/products/' . $product['id'] . '.json',json_encode($product));

    }

  }

  /**
   * Rimette i prodotti a magazzino per ordine eliminato
   */
  public static function addProducts(int $id) {

    $products = Products::list(['id_store_orders' => $id]);

    foreach($products as $product) {

      $quantity = $product['quantity'];

      $availability = $product['availability'];

      //Empty è disponibilità illimitata e continua diretta
      if(empty($product['availability_'.$availability]) && $product['availability_'.$availability] !== 0) {
        continue;
      }


      //TODO: Queste query sono solo due ma cosi si debugga meglio
      if(!empty($product['id_configurator']) && empty($product['id_store_products'])) {

        ConfiguratorWarehouse::updateAvailability($product['id_configurator'],'+',$availability,$quantity);

      } else {

        //Prodotto solo store
        if(!empty($product['id_store_products']) && empty($product['id_configurator'])) {
          StoreWarehouse::updateAvailability($product['id_store_products'],'+',$availability,$quantity);
        }
        
        //Prodotto store connesso a configuratore con segui disponibilità
        if(!empty($product['id_store_products']) && !empty($product['id_configurator']) && !empty((int)$product['rules_configurator_availability'])) {
          ConfiguratorWarehouse::updateAvailability($product['id_configurator'],'+',$availability,$quantity);
        }

          //Prodotto store connesso a configuratore senza segui disponibilità
        if(!empty($product['id_store_products']) && !empty($product['id_configurator']) && empty((int)$product['rules_configurator_availability'])) {
          StoreWarehouse::updateAvailability($product['id_store_products'],'+',$availability,$quantity);
        }

      }


    }

  }



  /**
   * Esporta i prodotti ordinati da una certa data in poi
   * TODO: da gestire i prodotti separati per reseller
   */
  public static function export(string $type) {

    $current_date = date("Y-m-d H:i:s");

    $last_date = Settings::get('orders','last_export_' . $type);

    $products = Shipping::queue($last_date,$type);

    $folder = uploads() . 'exports/orders/';

    $filename = $type . '-' . date('d-m-Y H-i') . '.csv';

    $export = array();

    $fp = fopen($folder . '/' .$filename, 'w');

    $headers = array(
      'Codice Scaffale',
      'Id Magazzino',
      'Id Prodotto',
      'Prodotto',
      'Fornitore',
      'Contenitore',
      'Capsula',
      'Formato',
      'Quantità',
      'Ordini',
      'Note'
    );

    fputcsv($fp, $headers);

    foreach($products as $product) {

        if($product['listing'] == 'custom') {
          continue;
        }

        $export = array(
          $product['shelf_location'],
          $product['id_configurator'],
          $product['id_store_products'],
          $product['title'],
          $product['manufacturer'],
          $product['container_title'],
          $product['capsule_color'],
          $product['container_capacity'],
          $product['total_quantity'],
          $product['orders']
        );

        fputcsv($fp, $export);

    }


    fclose($fp);

    Settings::set('orders','last_export_'.$type,$current_date);

    return true;

  }


	/**
	 * Genera documento PDF per spedizione (fattura o ddt)
	 *
	 * @param integer $id_shipping
	 * @param string $document
	 * @return void
	 */
	public static function invoice(array $order, array $values = [], $type = 'recepit'):array|bool {

		if(empty($order['id_users']) || !$user = Users::get(id($order['id_users']))) {
			throw new ValidatorException('user_not_found');
		}

    if(!$data = Data::generateFromUser($user,$order['type'])) {
      throw new ValidatorException('missing_invoice_data');
    }
		
		
		if(!$history = History::create($data)) {
			throw new ValidatorException('unable_to_create_invoice_data');
		}

		if(empty($values['date_invoice'])) {
			$date = date('Y-m-d');
		} else {
			$date = $values['date_invoice'];
		}

    //TODO: tutto questo blocco è da rimuovere quando gestite spedizioni da altra parte
    //Sovrascrivo dati utente con dati spedizione
    if($shipping = Shipping::getLast($order['id'])) {

      if(!empty($shipping['id_data_history']) && $shipping_data = History::get(id($shipping['id_data_history']))) {
        $data = $shipping_data;
      }

    }

    $shippings = Shipping::list(['id_store_orders' => $order['id']]);

		$filename = Invoices::fileName($order,$data,$type);
		
		//Set array with invoice data
		$invoice = array(
			'id_store_orders' => $order['id'],
			'id_data_history' => $history['id'],
			'number' => Invoices::getProgressive($type),
			'year' => date('Y'),
			'date_invoice' => $date,
			'type' => $type,
			'language' => $order['language'],
			'file_pdf' => $filename . '.pdf'
		);


		$products = Products::list(['id_store_orders' => $order['id'], 'report' => true]);

    $config =  config('invoices','default');

    /* TODO: USA riabilitare per fatturare con reseller usa, prendere dati fatturazione reseller o spedizione per ddt ma occhio a intesa san paolo, mettere dati bottle-up
    if(!empty($order['id_resellers'] && $store = Resellers::get($order['id_resellers']))) {
      $config = $store;
    } 
    */

    if($type == 'invoice_out') {

      //Calculate date expiry by payment modality
      $invoice['date_expiry'] = Invoices::calcExpiry($invoice['date_invoice'],$order['payment_modality']);

    }

    $vats = self::getVats($order['id']);

    //Aggiungo vat spedizioni a lista vats
    //TODO: questa è da sistemare con Samu per la questione arrotondamenti
    if(!empty($shipping['tax'])) {

      if(!isset($vats[$shipping['tax']])) {

        $vats[$shipping['tax']] = array(
          "tax" => $shipping['tax'],
          "total_price_final" => $shipping['price_final'],
          "total_price_final_taxes_excluded" =>  $shipping['price_final_taxes_excluded'],
          "total_price_final_taxes" =>  $shipping['price_final_taxes'],
          "total_taxes" => $shipping['price_taxes'],
          "total_to_pay" => $shipping['price_to_pay'],
          "total_price_taxes_excluded" => $shipping['price_taxes_excluded'],
          "total_discount" => 0,
          "id_store_tax" => $shipping['id_store_tax']
        );

      } else {

        $vats[$shipping['tax']]['total_price_final'] += $shipping['price_final'];
        $vats[$shipping['tax']]['total_price_final_taxes_excluded'] += $shipping['price_final_taxes_excluded'];
        $vats[$shipping['tax']]['total_price_final_taxes'] += $shipping['price_final_taxes'];
        $vats[$shipping['tax']]['total_to_pay'] += $shipping['price_to_pay'];
        $vats[$shipping['tax']]['total_price_taxes_excluded'] += $shipping['price_taxes_excluded'];
        $vats[$shipping['tax']]['total_taxes'] += $shipping['price_taxes'];

      }


    }

		$vars = [
			'order' => $order, 
			'products' => $products, 
			'invoice' => $invoice, 
			'data' => $data,
			'user' => $history, 
			'extra' => $values, 
			'config' => $config,
      'shippings' => $shippings,
      'vats' => $vats
		];

		if(!Pdf::generate($type,$filename, $vars, $order['language'])) { 
			throw new ValidatorException('pdf_creation_failed');
		}

    if($type == 'invoice_out' || $type == 'credit_note') {

      //TODO: questo è da sistemare per bene e da fare come PDF con array unico
      if(!Xsd::generate($filename, $invoice, $order, $products, $history, $vats, $shippings, $config)) { 
        throw new ValidatorException('pdf_creation_failed');
      }

      $invoice['file_xml'] = $filename . '.xml';

      $invoice['file_xsd'] = $filename . '.xsd';

    }

		return Invoices::create($invoice);

	}

  /**
   * Torna ive
   */
  public static function getVats(int $id):array {

    $vats = [];

    foreach(Db::getArray("SELECT store_orders_vats.*, store_tax.description, store_tax.description_short, store_tax.natura FROM store_orders_vats LEFT JOIN store_tax ON store_orders_vats.id_store_tax = store_tax.id WHERE id_store_orders = " . id($id)) as $vat) {

      $vats[$vat['tax']] = $vat;

    }

    return $vats;

  }
   
  /**
   * Invia notifiche di conferma ordine
   */
  public static function notify() {

  
    foreach(Db::getArray("SELECT id FROM store_orders WHERE status = 'confirmed' AND notified = 0") as $items) {

      if($order = self::get($items['id'],['id_resellers' => false])) {
     
        Db::update('store_orders','notified',1,'id',$order['id']);

        if($order['type'] == 'b2c' &&  empty($order['invoice_file_pdf'])) {

          if($invoice = self::invoice($order)) {
            $order['invoice_file_pdf'] = Invoices::downloadLink($invoice['file_pdf'],'outcoming','pdf');
          } else {
            $order['invoice_file_pdf'] = '';
          }

        }

        if(!empty($order['id_users'])) {
          Mailer::queue('order-complete', $order['id_users'], $order, false,1);
        } elseif (!empty($order['email_checkout'])) {
          Mailer::queue('order-complete', $order['email_checkout'], $order, false,1);
        }
 

      }

    }



  }

}

?>