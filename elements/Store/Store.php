<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Abstract\Decorator;
use Kodelines\Db;

/**
 * La classe store può avere una istanza signleton globale per tutte le chiamate istanziata di default con parametri base
 * Se si instanzia una nuova classe store vanno settati a mano tutti i parametri
 */

class Store extends Decorator {


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