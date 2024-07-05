<?php

declare(strict_types=1);

namespace Elements\Categories\Models;

use Kodelines\Abstract\Model;
use Kodelines\Oauth\Scope;

class CategoriesModel extends Model {

  public $table = 'categories';
  
  public $documents = 'categories';

  public $uploads = ['cover','icon_png'];

  public $view = 'store';

  public $meta = [
    'opengraph_type' => 'category'
  ];

  public function query(array $filters = []):string
  {

    $language = !empty($filters['language']) ? $filters['language'] : language();

 
    $query = "
      SELECT
       categories.*,
       CASE WHEN id_categories_main IS NULL THEN 1 ELSE 0 END AS main,
       ml.slug AS main_slug,
       ml.title AS main_title,
       ";
       if(!empty($filters['type'])) {

        $query .= encode($filters['type']) . " AS type, ";

        if($filters['type'] == 'store') {
          $query .= "(SELECT COUNT(p.id) FROM store_products p WHERE p.id_categories = categories.id AND p.status = 'on_sale') AS items,";
        }  else {
          $query .= "NULL AS items,";
        }
        
      } else {
        $query .= "NULL AS items,";
        $query .= " categories.type, ";
      }

      
      if(!empty($filters['landing']))  {    
        $query .= "CAST(categories_landings.position) AS INT, ";
      } else {
        $query .= "NULL AS position,";
      }


      $query .= "
      categories_lang.*
      FROM
       categories 
      JOIN categories_lang ON categories.id = categories_lang.id_categories
      LEFT JOIN categories_lang ml ON ml.id_categories = categories.id_categories_main AND ml.language = ".encode($language);

      if(!empty($filters['landing']))  {
       
        $query .= " JOIN categories_landings ON categories_landings.id_categories = categories.id AND categories_landings.landing = " . encode($filters['landing']);
      
        if(!empty($filters['position']))  {
          $query .= " AND FIND_IN_SET(categories_landings.position," . encode($filters['position']) . ")";
        }

        if(!empty($filters['evidence']))  {
          $query .= " AND categories_landings.evidence = 1";
        }

        $filters['orderby'] = 'categories_landings.position ASC';

      }

      $query .= "
      WHERE categories_lang.language = ". encode($language);

      if(!empty($filter['status']) && !Scope::is('administrator',true)) {

        if($filters['status'] == 'visible') {
          $query .= " AND (categories.status = 'published' OR categories.status = 'hidden') ";
        } else {
          $query .= " AND categories.status = " .encode($filters['status']);
        }

        unset($filters['status']);
      }

      if(isset($filters['main'])) {

        if($filters['main'] == 1) {
          $query .= " AND categories.id_categories_main IS NULL";
        } else {

          if(!isset($filters['id_categories_main'])) {
            $query .= " AND categories.id_categories_main IS NOT NULL";
          }
     
        }

      }



   
      $query.= $this->applyFilters($filters);

      return $query;
  }

}

?>