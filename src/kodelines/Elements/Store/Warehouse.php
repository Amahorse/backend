<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Db;
use Kodelines\Abstract\Decorator;

class Warehouse extends Decorator  {



  /**
   * Genera codice prodotto univoco
   *
   * @param integer $id_store_products
   * @param integer $id_manufacturers
   * @param string $type
   * @return void
   */
  public static function generateCode(int $id_store_products, int|null $id_manufacturers,string $type, string $listing) {

    return 'PR' . $id_store_products . substr(mb_strtoupper($type),0,2) . 'M' . (int)$id_manufacturers . substr(mb_strtoupper($listing),0,2);

  }



    /**
   * Controlla quale disponibilità è la maggiore con precedenza alla virtuale
   *
   * @param array $product
   * @return string
   */
  public static function getAvailabilityType(array $product):string {
  
    //Tutto Infinito
    if($product['availability_virtual'] === NULL && $product['availability_warehouse'] === NULL) {
      return 'warehouse';
    }

    //Tutto Esaurito
    if($product['availability_virtual'] === 0 && $product['availability_warehouse'] === 0) {
      return 'virtual';
    }

    //Illimitato in magazzino
    if($product['availability_warehouse'] === NULL) {
      return 'warehouse';
    }

    //Esaurito in magazzino, virtuale illimitata
    if($product['availability_warehouse'] === 0 && $product['availability_virtual'] === NULL) {
      return 'virtual';
    }

    //Priorità a magazzino se ci sono tutte e due e quantità è minore dei prodotti in magazzino
    if($product['quantity'] <= $product['availability_warehouse']) {
      return 'warehouse';
    }

    //Altrimenti ritorna virtuale
    return 'virtual';
  }

  /**
   * Controlla la disponibilità massima tra magazzino e virtuale 
   *
   * @param array $product
   * @return int
   */
  public static function getAvailabilityMax(array $product):int|null {

    if($product['availability_virtual'] === null || $product['availability_warehouse'] === null) {
      return null;
    }


    return (int)((int)$product['availability_virtual'] + (int)$product['availability_warehouse']);
  }


  /**
   * Prodotti per reseller
   *
   * @param integer $id
   * @return void
   */
  public static function reseller(int $id) {

    return Db::getArray("SELECT * FROM store_products_resellers WHERE id_resellers = " . $id);

  }

  /**
   * Aggiorna disponibilità magazzino
   *
   * @param string $operator
   * @param integer $quantity
   * @return boolean
   */
  public static function updateAvailability(int $id, string $operator, string $availabilityType, int $quantity):bool {
    return Db::query("UPDATE store_products SET availability_".$availabilityType." = availability_".$availabilityType." ".$operator." ".$quantity." WHERE availability_".$availabilityType." IS NOT NULL AND id = " . $id);
  }


}

?>