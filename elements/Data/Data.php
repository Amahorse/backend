<?php

declare(strict_types=1);

namespace Elements\Data;

use Kodelines\Db;
use Kodelines\Helpers\Countries;
use Kodelines\Abstract\Decorator;
use Elements\Users\Users;

class Data extends Decorator {



  /**
   * Ritorna dati fatturazione in base a varie modalità
   *
   * @param string $table
   * @param string $id
   * @param string $mode    all = lista di tutte, extra = lista dati aggiuntivi, main = elemento singolo dati principali
   * @return array|boolean
   */
  public static function getByElement(string $table, int $id, $mode = 'all'): array|bool {

    if($mode == 'all') {
      return Db::getArray("SELECT * FROM data WHERE table_id = " .id($id) . " AND table_name = " . encode($table) . " ORDER BY main ASC");
    }

    if($mode == 'extra') {
      return Db::getArray("SELECT * FROM data WHERE main <> 1 AND table_id = " .id($id) . " AND table_name = " . encode($table) . " ORDER BY main ASC");
    }

    if($mode == 'main') {
      return Db::getRow("SELECT * FROM data WHERE main = 1 AND table_id = " .id($id) . " AND table_name = " . encode($table));
    }

    return false;

  }

  /**
   * Genera header e altri dati da dati inseriti in tabella users
   *
   * @param array $data
   * @param string $type
   * @return boolean|array
   */
  public static function generateFromUser(array $user, string $type): bool|array {

    //Prendo dati predefiniti da tabella utenti
    $data = array_merge(Users::get(id($user['id'])),$user);
 
    if(empty($data['header'])) {

      //Check header base on user type
      if($type == 'b2c') {

        //First name
        if(empty($data['first_name'])) {
          return false;
        }

        //Last name
        if(empty($data['last_name'])) {
          return false;
        }

        $data['header'] = $data['first_name'] . ' ' . $data['last_name'];

      } else {

        //Business
        if(empty($data['business_name'])) {
          return false;
        }

        $data['header'] = $data['business_name'];

      }

    }

    //Phone prefix fix by country
    if(!empty($data['id_countries']) && empty($data['phone_prefix']) && !empty($data['phone']) && $phone_prefix = Countries::getPhonePrefix($data['id_countries'])) {
      $data['phone_prefix'] = $phone_prefix;
    }


    return $data;

  }


  /**
   * Imposta un nuovo indirizzo di spedizione
   *
   * @param integer $id
   * @param string $table
   * @param array $values
   * @return array|boolean
   */
  public static function set(int $id, string $table, array $values, $old_id = 0):array|bool {

    $values['table_id'] = $id;

    $values['table_name'] = $table;

    if(self::validate($values)) {

      if(empty($old_id)) {

        return self::create($values,true);

      } else {

        if($values = self::update($old_id,$values)) {

          if(!empty($values['main'])) {
            Db::query("UPDATE data SET main = 0 WHERE table_id = ".encode($id)." AND table_name = ".encode($table)." AND id <> ". id($old_id));
          }

        }
        
        return $values;
      }

    }

    return false;

  }




  /**
   * Ritorna dati di spedizione del magazzino per DDT
   * TODO: differenziare in caso di id resellers e id_stores settato
   * @return array
   */
  public static function pickup():array {
    return config('shipping','default');
  }


}

?>