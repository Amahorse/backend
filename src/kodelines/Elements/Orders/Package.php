<?php

declare(strict_types=1);

namespace Elements\Orders;

use Elements\Store\Warehouse;


class Package  {

  /**
   * Aggiunge componenti a prodotto pacchetto nel carrello, 
   *
   * @param array $main
   * @return array
   */
  public static function generate(array $main, array $order):array {

    $components = Warehouse::getChild('components',$main['id_store_products']);

    $inserted = [];

    //Ciclo i prodotti all'interno del magazzino
    foreach($components as $component) {

        //Fix per id ambiguo su addComponent;
        unset($component['id']);

        //Fix per prodotti con componenti fissi
        if(!empty($component['id_store_products_component'])) {
            $component['id_store_products'] = $component['id_store_products_component'];
        }

        $component['fixed_component'] = 1;

        if($component = Products::addComponent($main,$component,$order,false)) {
            $inserted[] = $component;
        }


    } 

    return $inserted;

  }

   /**
   * Modifica prodotti pacchetto già nel carrello
   *
   * @param array $main //Va fatto sempre il full get che gli serve l'array components
   * @return array
   */
  public static function update(array $main, array $order):array {


    if(!isset($main['components'])) {
        return [];
    }

    $inserted = [];

    //Faccio loop dei prodotti già inseriti per recuperarmi l'id da modificare
    foreach($main['components'] as $component) {

        if($component = Products::addComponent($main,$component,$order)) {
            $inserted[] = $component;
        }

    }

    return $inserted;

  }

   
}

?>