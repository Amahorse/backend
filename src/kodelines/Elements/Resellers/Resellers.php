<?php

declare(strict_types=1);

namespace Elements\Resellers;

use Kodelines\Db;
use Elements\Users\Users;
use Kodelines\Abstract\Decorator;
use Kodelines\Oauth\Scope;

class Resellers extends Decorator {

    /**
     * Associa o inserisce un profilo reseller a un id utente 
     *
     * @param integer $id_users
     * @return integer|boolean
     */
    public static function createByUser(int $id_users):array|bool {

      if(!$user = Users::get($id_users)) {
        return false;
      }

      //Check if the agent was previously inserted on db and is with status 0
      if($exists = self::getByUser($id_users)) {

          if(!Db::update('resellers','active',1,'id_users',$id_users)) {
              return false;
          }

          return $exists;
      }

      return self::create(['id_users' => $id_users, 'email_contacts' => $user['email'], 'email_orders' => $user['email']]);

  }


  /**
   * Controlla lo stato di un reseller, ritorna false se stato a 0 o id non trovato
   *
   * @param integer $id_resellers
   * @return array|boolean
   */
  public static function isActive(int $id_resellers) : array|bool {
    return Db::getRow("SELECT * FROM resellers WHERE id_resellers = " .id($id_resellers) . " AND active = 1");
  }

    
  /**
   * Get a item by user id, return false if not found or array with values
   *
   * @method get
   * @param  int    $id user id
   * @return array|bool
   */
  public static function getByUser(int $id_users) : array|bool {
    return Db::getRow("SELECT * FROM resellers WHERE id_users = " .id($id_users) . " AND active = 1");
  }



 /**
   * Applica filtri id reseller a tutte le query
   *
   * @param  array    $filters
   * @return array
   */
  public static function addFilters(array $filters = array()) : array {

    //Filtro reseller è fisso per tutte le tabelle
    if(!isset($filters['id_resellers'])) { 
      
      if(defined('_ID_RESELLERS_')) {
        $filters['id_resellers'] = _ID_RESELLERS_;
      } elseif(!Scope::is('administrator',true)) {
        $filters['id_resellers'] = NULL;
      }

    }

    //Id resellers a false vuol dire che non distingue tra resellers o che è già stato prefiltrato
    if(isset($filters['id_resellers']) && $filters['id_resellers'] === false) {
      unset($filters['id_resellers']);
    }



    return $filters;
  }

}

?>