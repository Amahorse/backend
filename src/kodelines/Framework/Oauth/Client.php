<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use Kodelines\Key;
use Kodelines\Db;

class Client
{
     /**
     * Torna client
     *
     * @param int id
     * @return boolean|string
     */
    public static function get(int $id): bool|array {
        return Db::getRow("SELECT * FROM oauth_clients WHERE id = " . id($id));
    }

    /**
     * Torna client secret per client id
     *
     * @param string $client_id
     * @return boolean|string
     */
    public static function check(string $client_id, $active = false): bool|array {

        $query = "SELECT * FROM oauth_clients WHERE client_id = " .encode($client_id);

        if($active) {
            $query  .= " AND status = 1";
        }
    
        return Db::getRow($query);
    }
  
    /**
     * Genera cliet_id, client_secret e token
     *
     * @param array $store
     * @return array
     */
    public static function generate():array
    {
        
        $token = [
            'client_id' => Key::generate(),
            'client_secret' => Key::generate()
        ];

        return $token;
        
    }

    /**
     * Lista dei client abilitati
     *
     * @return array
     */
    public static function getKids():array
    {

       //TODO: mettere in cache
       $clients = [];

       foreach(Db::getArray("SELECT * FROM oauth_clients WHERE status = 1") as $client) {
          $clients[$client['kid']] = $client['client_secret'];
       }

       return $clients;
        
    }


    /**
     * Lista dei client abilitati
     *
     * @return array
     */
    public static function list():array
    {
       return Db::getArray("SELECT oauth_clients.*, stores.name FROM oauth_clients LEFT JOIN stores ON stores.id = oauth_clients.id_stores");   
    }

}
