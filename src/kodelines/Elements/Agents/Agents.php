<?php

declare(strict_types=1);

namespace Elements\Agents;

use Kodelines\Db;
use Elements\Users\Users;
use Kodelines\Abstract\Decorator;

class Agents extends Decorator {



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

            if(!Db::update('agents','status',1,'id_users',$id_users)) {
                return false;
            }

            return $exists;
        }

        return self::create(['id_users' => $id_users]);

    }


  /**
   * Controlla lo stato di un agente, ritorna false se stato a 0 o id non trovato
   *
   * @param integer $id_agents
   * @return array|boolean
   */
  public static function isActive(int $id_agents) : array|bool {
    return Db::getRow("SELECT * FROM agents WHERE id_agents = " .id($id_agents) . " AND active = 1");
  }
    
  /**
   * Get a item by user id, return false if not found or array with values
   *
   * @method get
   * @param  int    $id user id
   * @return array|bool
   */
  public static function getByUser(int $id_users) : array|bool {
    return Db::getRow("SELECT * FROM agents WHERE id_users = " .id($id_users) . " AND active = 1");
  }



}

?>