<?php

declare(strict_types=1);

namespace Elements\Clients;

use Kodelines\Abstract\Decorator;
use Kodelines\Key;

class Clients extends Decorator {


      
    /**
     * Genera cliet_id, client_secret e token
     *
     * @param array $store
     * @return array
     */
    public static function generate():array
    { 
        return [
            'client_id' => Key::generate(),
            'client_secret' => Key::generate()
        ];   
    }

}

?>