<?php

declare(strict_types=1);

namespace Elements\Brands;

use Kodelines\Abstract\Decorator;
use Kodelines\Db;

class Brands extends Decorator  {


    public static function getCodes():array {

        $exists = [];

        foreach(Db::getArray("SELECT id,code FROM brands") as $value) {
            $exists[$value['code']] = $value['id'];
        }
        
        return $exists;
    }


}

?>