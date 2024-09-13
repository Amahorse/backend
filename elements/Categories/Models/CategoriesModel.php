<?php

declare(strict_types=1);

namespace Elements\Categories\Models;

use Kodelines\Abstract\Model;

class CategoriesModel extends Model {

  public $table = 'categories';
  
  public $documents = 'categories';

  public $uploads = ['cover','icon_png'];

  public function query(array $filters = []):string
  {

    $language = !empty($filters['language']) ? $filters['language'] : language();

 
    $query = "
      SELECT
       categories.*,
       CASE WHEN id_categories_main IS NULL THEN 1 ELSE 0 END AS main,
       ml.slug AS main_slug,
       ml.title AS main_title,
      categories_lang.*
      FROM
       categories 
      JOIN categories_lang ON categories.id = categories_lang.id_categories
      LEFT JOIN categories_lang ml ON ml.id_categories = categories.id_categories_main AND ml.language = ".encode($language);

      $query.= $this->applyFilters($filters);

      return $query;
  }

}

?>