<?php

declare(strict_types=1);

namespace Elements\Stores\Models;

use Kodelines\Abstract\Model;

class StoresModel extends Model {

  public $table = 'stores';

  public $documents = 'stores';

  public $uploads = ["image"];

  public $defaults = [];

  public $validator = [
    "email" => ["email"],
    "phone" => ["phone"]
  ];

  /**
   * Query principale
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string {

    if (!empty($filters['lang'])) {
      $language = $filters['lang'];
    } else {
      $language = _APP_LANGUAGE_;
    }

    $query = "
    SELECT
        stores.*,
        stores.id AS id_stores,
        stores_lang.*,
        countries.country,
        countries.iso AS country_short,
        oauth_clients.kid,
        oauth_clients.client_id
    FROM stores 
        LEFT JOIN countries ON stores.id_countries = countries.id
        LEFT JOIN oauth_clients ON stores.id = oauth_clients.id_stores
    WHERE stores.id IS NOT NULL
    ";

    if(!empty($filters['kid'])) {
      $query .= " AND oauth_clients.kid = " . encode($filters['kid']);
    }


    $query.= $this->applyFilters($filters);

    return $query;

  }





}

?>