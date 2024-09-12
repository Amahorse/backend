<?php

declare(strict_types=1);

namespace Elements\Stores;

use Kodelines\Db;
use Kodelines\Abstract\Decorator;

class Stores extends Decorator {

    /**
     * Torna negozio in base a Kid
     *
     * @param string $kid
     * @return bool
     */
    public static function getByKid(string $kid) {
        return Db::getRow(self::query(["kid" => $kid, "status" => "active", "id_resellers" => false]));
    }


}

?>