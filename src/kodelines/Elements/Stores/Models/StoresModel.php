<?php

declare(strict_types=1);

namespace Elements\Stores\Models;

use Kodelines\Abstract\Model;

class StoresModel extends Model {

  public $table = 'stores';

  public $documents = 'stores';

  public $uploads = ["image"];

  public $validator = [
    "email" => ["email"],
    "phone" => ["phone"]
  ];

  /**
   * Setto default con le impostazioni dal constructor
   */
  public function __construct()
  {
    parent::__construct();

    /**
     * Campi predefiniti
     */
    $this->defaults = [
      "id_countries" => config('default','id_countries'),
      "status" => "active",
      "checkout_mode" => "configurator",
      "payment_commission_percentage" => config('store','payment_commission_percentage'),
      "shipping_max_hour" => config('store','shipping_max_hour'),
      "shipping_delay" => 1
    ];

  }



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
      $language = language();
    }

    $query = "
    SELECT
        stores.*,
        stores.id AS id_stores,
        stores_lang.*,
        resellers.id_manufacturers,
        resellers.logo,
        resellers.cover,
        resellers_lang.title AS reseller_title,
        resellers_lang.description AS reseller_description,
        resellers_lang.content AS reseller_content,
        countries.country,
        countries.iso AS country_short,
        oauth_clients.kid,
        oauth_clients.client_id
    FROM stores 
        LEFT JOIN resellers ON stores.id_resellers = resellers.id
        LEFT JOIN stores_lang ON stores_lang.id_stores = stores.id AND stores_lang.language = ".encode($language)."
        LEFT JOIN resellers_lang ON resellers_lang.id_resellers = resellers.id AND resellers_lang.language = ".encode($language)."
        LEFT JOIN countries ON stores.id_countries = countries.id
        LEFT JOIN oauth_clients ON stores.id = oauth_clients.id_stores
    WHERE stores.id IS NOT NULL
    ";

    if(!empty($filters['kid'])) {
      $query .= " AND oauth_clients.kid = " . encode($filters['kid']);
    }

    if(empty($filters['groupby'])) {
      $filters['groupby'] = 'stores.id';
    }

    $query.= $this->applyFilters($filters);

    return $query;

  }





}

?>