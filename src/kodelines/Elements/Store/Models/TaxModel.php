<?php

declare(strict_types=1);

namespace Elements\Store\Models;

use Kodelines\Abstract\Model;

class TaxModel extends Model {

  public $table = 'store_tax';

  public $defaults = [
    "id_countries" => NULL,
    'tax_type' => 'percentage',
    'community' => null
  ];
 
  public $validator = ['name' => ['required']];

  public function query(array $filters = []):string
  {

    $query = "
      SELECT 
      store_tax.*, 
      c.iso, 
      CASE WHEN store_tax.community IS NOT NULL THEN store_tax.community ELSE c.country END AS country 
      FROM store_tax 
      LEFT JOIN countries c ON c.id = store_tax.id_countries 
      WHERE store_tax.main = 1 
    ";

      $filters['groupby'] = "store_tax.group_uniq";

      $filters['orderby'] = "store_tax.tax DESC";

    $query.= $this->applyFilters($filters);

    return $query;
  }

}

?>