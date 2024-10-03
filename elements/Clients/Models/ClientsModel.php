<?php

declare(strict_types=1);

namespace Elements\Clients\Models;

use Kodelines\Abstract\Model;

class ClientsModel extends Model {

  public $table = 'oauth_clients';

  public $childs = ['origins'];


  /**
   * Query principale
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string {


    $query = "
    SELECT 
        oauth_clients.client_id,
        oauth_clients.client_secret,
        oauth_clients.id_stores, 
        oauth_clients.kid,
        stores.*
    FROM 
        oauth_clients 
    JOIN stores ON stores.id = oauth_clients.id_stores
    WHERE oauth_clients.id IS NOT NULL
    ";


    $query.= $this->applyFilters($filters);

    return $query;

  }





}

?>