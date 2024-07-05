<?php


declare(strict_types=1);

namespace Elements\Tracking;

use Kodelines\Db;
use Kodelines\Tools\Client;

class Ban  {


    /**
     * Banna un ip o se null ip corrente
     *
     * @param string|null $ip
     * @return bool
     */
    public static function IP(string $ip = null):bool {

        if(is_null($ip)) {
            $ip =  Client::IP();
        }

        return Db::replace('tracking_ipban',['ip' => $ip]);
    }   


    /**
     * Controlla se ip è bannato ritorna il contrario perchè se non trovato deve tornare true
     *
     * @param string|null $ip
     * @return bool
     */
    public static function checkIP(string $ip = null):bool {

        if(is_null($ip)) {
            $ip = Client::IP();
        }

        return !Db::getValue("SELECT ip FROM tracking_ipban WHERE ip = " . encode($ip));
    }   




}

?>