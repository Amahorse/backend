<?php

declare(strict_types=1);

namespace Elements\Store\Models;

use Kodelines\Abstract\Model;
use Elements\Resellers\Resellers;

class DiscountsModel extends Model {


  public $table = 'store_discounts';

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
      "status" => 1,
      "client_type" => 'all'
    ];

  }


   /**
    * Form inputs required
    *
    * @type array
    */
  public  $validator = [
    'name' => ['required'],
    'discount_code' => ['unique']
  ];


  public function query(array $filters = []):string
  {

    $query = "
      SELECT 
        store_discounts.*, 
        countries.iso, 
        countries.country, 
        CASE WHEN agent.business_name IS NOT NULL THEN agent.business_name ELSE CONCAT(agent.first_name,' ',agent.last_name) END AS agent,
        resellers_lang.title AS reseller,
        resellers.url AS reseller_url,
        ( SELECT COUNT(id_users) FROM store_discounts_users WHERE id_store_discounts = store_discounts.id) AS usages 
      FROM store_discounts 
      LEFT JOIN resellers ON store_discounts.id_resellers = resellers.id
      LEFT JOIN resellers_lang ON store_discounts.id_resellers = resellers_lang.id_resellers AND resellers_lang.language = ".encode(language())."
      LEFT JOIN agents ON store_discounts.id_agents = agents.id
      LEFT JOIN users agent ON agents.id_users = agent.id 
      LEFT JOIN countries ON countries.id = store_discounts.id_countries
      WHERE store_discounts.id IS NOT NULL ";

      //Prende solo sconti attivi su intervallo data corrente
      if(!empty($filters['valid'])) {

        $query .= " AND (store_discounts.date_end >= DATE(NOW()) OR store_discounts.date_end IS NULL) ";

        $query .= " AND (store_discounts.date_start <= DATE(NOW()) OR store_discounts.date_start IS NULL) ";
        
      }

      if(!empty($filters['mode'])) {
        
        $query .= " AND FIND_IN_SET(".encode($filters['mode']).",store_discounts.mode) ";

        unset($filters['mode']);
      }

      $query.= $this->applyFilters($filters);

      return $query;

  }


}

?>