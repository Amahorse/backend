<?php

declare(strict_types=1);

namespace Elements\Store\Helpers;


class Client   {

  /**
   * Ritorna tipi di clienti disponibili per lo store
   *
   * @param array $types
   * @return array
   */
  public static function filter(array $types):array {

    if(config('store','enable_b2c') == false && in_array('b2c',$types)) {
        unset($types['b2c']);
    }

    if(config('store','enable_b2b') == false && in_array('b2b',$types)) {
        unset($types['b2b']);
    }

    if(config('store','enable_horeca') == false && in_array('horeca',$types)) {
        unset($types['horeca']);
    }

    return $types;
  }



}

?>