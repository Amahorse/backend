<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use Kodelines\Db;
use Kodelines\Error;
use Kodelines\Helpers\Cache;
use Elements\Clients\Clients;

class Client
{


    /**
     * Torna client secret per client id
     *
     * @param string $client_id
     * @return boolean|string
     */
    public static function get(string $client_id): bool|array {
      return Db::getRow(Clients::query(['client_id' => $client_id,'status' => 1]));
    }


    /**
     * Lista dei client abilitati
     *
     * @return mixed
     */
    public static function getKids():mixed
    {


       if(!$clients = Cache::getInstance()->getArray('oauth_clients')) {

         $clients = [];

         foreach(Db::getArray("SELECT * FROM oauth_clients WHERE status = 1") as $client) {
            $clients[$client['kid']] = $client['client_secret'];
         }
  
         Cache::getInstance()->setArray($clients,'oauth_clients');
       }

       if(count($clients) == 0) {
          return new Error('No clients found');
       }

       if(count($clients) == 1) {
          return array_values($clients)[0];
       }

       return $clients;
        
    }

        /**
     * Lista dei client abilitati
     *
     * @return array
     */
    public static function getAllowedOrigins(string $client_id):array
    {

       if($origins = Cache::getInstance()->getArray('oauth_origins_' . $client_id)) {
          return $origins;
       }

       $origins = Db::getArray("SELECT * FROM oauth_clients WHERE id_oauth_clients = " . id($client_id));

       Cache::getInstance()->setArray($origins,'oauth_origins_' . $client_id);

       return $origins;
        
    }




}
