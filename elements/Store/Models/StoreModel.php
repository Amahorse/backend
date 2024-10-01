<?php

declare(strict_types=1);

namespace Elements\Store\Models;

use Kodelines\Abstract\Model;

class StoreModel extends Model {

  public $table = 'store_products';
  
  public $documents = 'products';

  public $uploads = ['cover'];

  public $childs = ['components','discounts','availability'];


  public $validator = [
    'status' => ['required'],
    'variant' => ['required']
  ];


  public function query($filters = []):string {

    $language = !empty($filters['language']) ? $filters['language'] : _APP_LANGUAGE_;

    if(user('id_stores')) {
      $id_stores = user('id_stores');
    } else {
      $id_stores = config('store','id_stores');
    }

    
    $query = "SELECT 
      store_products.*,
      store_products.id AS id_store_products,
      products.*,
      products.id AS id,
      stores.currency,
      stores.taxes_included,
      brands.title AS brand,
      brands.code AS brand_code,
      CASE WHEN stores.type = 'b2b' THEN store_products_availability.availability_b2b ELSE store_products_availability.availability_b2c END AS availability,
      store_products_prices.price,
      store_products_discounts.discount_offer_percentage,";

      if(user()) {
        $query .= "      
        ".(float)user('discount_client_percentage')." AS discount_client_percentage,
        ".(float)user('discount_contract_percentage')." AS discount_contract_percentage,
        ".(float)user('discount_final_percentage')." AS discount_final_percentage,";
      } else {
        $query .= "      
          NULL AS discount_client_percentage,
          NULL AS discount_contract_percentage,
          NULL AS discount_final_percentage,";
      }

    $query .= 
        "products_lang.*,
        categories_lang.title AS category,
        categories_lang.id_categories
        FROM store_products
          JOIN products ON store_products.id_products = products.id
          JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = ".encode($language)."
          JOIN store_products_availability ON store_products_availability.id_store_products = store_products.id
          JOIN store_products_prices ON store_products_prices.id_store_products = store_products.id AND store_products_prices.id_stores = ". id($id_stores) ."
          JOIN stores ON stores.id = ". id($id_stores) ."
          LEFT JOIN brands ON brands.id = products.id_brands
          LEFT JOIN store_products_discounts ON store_products_discounts.id_store_products = store_products.id AND imported = 1
          LEFT JOIN products_categories ON products_categories.id_products = products.id
          LEFT JOIN categories_lang ON categories_lang.id_categories = products_categories.id_categories AND categories_lang.language = ".encode($language)."
        WHERE store_products.id IS NOT NULL ";

      if(!empty($filters['id_categories'])) {
        $query .= " AND store_products.id_products IN (SELECT id_products FROM products_categories WHERE products_categories.id_categories = ".encode($filters['id_categories']). ")";
      }

      if(!empty($filters['slug'])) {
        $query .= " AND products_lang.slug = ".encode($filters['slug']);
      }

      if(!empty($filters['brand_code'])) {
        $query .= " AND brands.code = ".encode($filters['brand_code']);
      }

      if(!empty($filters['code'])) {
        $query .= " AND products.code = ".encode($filters['code']);
      }
      
      $query .= $this->applyFilters($filters); 

      return $query;

  }






}

?>