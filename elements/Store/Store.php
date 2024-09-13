<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Abstract\Decorator;
use Elements\Store\Helpers\Price;
use Kodelines\Db;

/**
 * La classe store può avere una istanza signleton globale per tutte le chiamate istanziata di default con parametri base
 * Se si instanzia una nuova classe store vanno settati a mano tutti i parametri
 */

class Store extends Decorator {

  public static function split(array $store):array {

    $data = [];

    $product = array_keys(self::getFields(new \Elements\Products\Models\ProductsModel));

    $variant = array_keys(self::getFields(new \Elements\Store\Models\StoreModel));

    foreach($store as $value) {

      $value = array_merge($value,Price::calculate($value));
      
      if(!isset($data[$value['code']])) {

        $data[$value['code']] = array_intersect_key($value, array_flip($product));

        $data[$value['code']]['variants'] = [];

      }

      $data[$value['code']]['variants'][] = array_intersect_key($value, array_flip($variant));

    }

    return array_values($data);

  }

  public static function getSkus():array {

    $exists = [];

    foreach(Db::getArray("SELECT id,sku FROM store_products") as $value) {
        $exists[$value['sku']] = $value['id'];
    }
    
    return $exists;
  }

  public static function generateSku(array $values): string {
    
    if(empty($values['variant'])) {
        return $insert['sku'] = $values['code'];
    } 
    
    return  $values['code'] . '_' . str_replace(' ','',trim($values['variant']));  

  }


}

?>