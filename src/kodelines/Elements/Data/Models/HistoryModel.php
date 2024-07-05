<?php

declare(strict_types=1);

namespace Elements\Data\Models;

use Kodelines\Abstract\Model;

class HistoryModel extends Model {

  public $table = 'data_history';

  public function query($filters = []):string {

    
    $query = "  
    SELECT data_history.*, 
    co.country, 
    co.iso AS country_short, 
    pr.state, 
    pr.state_short 
    FROM data_history 
    LEFT JOIN countries co ON data_history.id_countries = co.id 
    LEFT JOIN countries_states pr ON data_history.id_countries_states = pr.id 
    WHERE data_history.id IS NOT NULL ";

    $query .= $this->applyFilters($filters);

    return $query;
  }



}

?>