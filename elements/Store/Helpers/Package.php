<?php

declare(strict_types=1);

namespace Elements\Store\Helpers;

use Kodelines\Db;
use Elements\Store\Store;
class Package  {

      /**
   * Prende i componenti di un prodotto pacchetto per visualizzazione
   *
   * @param int $id_store_products
   * @return array
   */
  public static function getComponents(int $id_store_products, $price = true):array {

    $components =  [];
   
    //Ciclo i prodotti all'interno del magazzino
    foreach(Db::getArray("SELECT store_products_components.*, store_products.listing FROM store_products_components JOIN store_products ON store_products.id = store_products_components.id_store_products_component WHERE id_store_products = " . $id_store_products) as $component) {

        if($cmp = self::getComponent($component,$price)) {
            $components[] = $cmp;
        }
    } 


    return $components;

  }

  /**
   * Get componente singolo
   *
   * @param Array $component
   * @return array|bool
   */
  public static function getComponent(array $component, $price = true):array|bool {

    Store::setQuantity($component['quantity']);
    
    //Non prendo prezzi perchè mi serve solo il prezzo iva esclusa da sommare a quello base del pacchetto
    if($cmp = Store::get($component['id_store_products_component'],$price)) {
        return array_merge($component,$cmp);
    }
    
    return false;
  }

  /**
   * Fixa parametri componenti in base agli input
   *
   * @param Array $data
   * @return array
   */
  public static function fixComponents(array $data):array {

    $components = [];
  
    foreach($data as $component) {
  
      $component['follow_quantity'] = 1;

      $component['fixed_component'] = 1;
  
      $components[] = $component;
    }
  
    return $components;
  }
  
  /**
   * Ritorna prezzo pacchetto e fixa disponibilità 
   *
   * @param Array $package
   * @return array
   */
  public static function getValues(array $package):array {

    
    $recalc = false;

    $old_price_base = (float)$package['price_base'];

    $priceBase = (float)$package['price_base'];

    foreach($package['components'] as $component) {

   
        if($component['add_price'] == 1) {

            $recalc = true;

            $priceBase += (float)$component['price_taxes_excluded'];

        } 
        
    
    }

    //Ricalcolo prezzo totale del pacchetto
    
    if($recalc) {

      $price = new Price;

      if(defined('_CLIENT_TYPE_')) {
          $price->setClientType(_CLIENT_TYPE_);
      }

      $package['price_taxes_excluded'] = $priceBase;

      //Questo calcolo non converte perchè parte dal prezzo iva esclusa già convertito del pacchetto e dei componenti
      $package = array_merge($package,$price->calculate($package,$package['quantity'],false));
      
      //Mantengo prezzo base originale
      $package['price_base'] = $old_price_base;

    }
  
  
    return $package;
  
    }
}

?>