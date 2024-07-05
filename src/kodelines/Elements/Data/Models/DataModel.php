<?php

declare(strict_types=1);

namespace Elements\Data\Models;

use Kodelines\Abstract\Model;

class DataModel extends Model {

  public $table = 'data';

  public function query($filters = []):string {

    
    $query = "  
    SELECT data.*, 
    co.country, 
    co.iso AS country_short, 
    pr.state, 
    pr.state_short 
    FROM data
    LEFT JOIN countries co ON data.id_countries = co.id 
    LEFT JOIN countries_states pr ON data.id_countries_states = pr.id 
    WHERE data.id IS NOT NULL ";

    $query .= $this->applyFilters($filters);

    return $query;
  }


  /**
   * Setto default con le impostazioni dal constructor
   */
  public function __construct()
  {
    parent::__construct();

    $this->defaults['id_countries'] = config('default','id_countries');
  
  }


  public $validator = [
    "header" => ["required"],
    "address" => ["required"],
    "city" => ["required"],
    "zip_code" => ["required","zip_code"],
    "id_countries" => ["required"],
    "phone" => ["phone"]
  ];

  


}

?>