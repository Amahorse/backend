<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Db;
use Kodelines\Abstract\Decorator;

class Tax extends Decorator  {



  public static function getByGroup(string $group) {
    return Db::getArray("SELECT * FROM store_tax WHERE group_uniq = ". encode($group));
  }

  public static function getByType(string $taxation_type, int $id_countries, string $client_type) {
    return Db::getRow("SELECT * FROM store_tax WHERE FIND_IN_SET(".encode($taxation_type).",taxation_type) AND FIND_IN_SET(".encode($client_type).",client_type)  AND id_countries = ". $id_countries);
  }



}

?>