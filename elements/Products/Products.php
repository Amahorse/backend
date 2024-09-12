<?php

declare(strict_types=1);

namespace Elements\Products;

use Kodelines\Abstract\Decorator;
use Kodelines\Db;

class Products extends Decorator  {


    public static function getCodes():array {

        $exists = [];

        foreach(Db::getArray("SELECT id,code FROM products") as $value) {
            $exists[$value['code']] = $value['id'];
        }
        
        return $exists;
    }

}

?>