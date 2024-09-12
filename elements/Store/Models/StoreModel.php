<?php

declare(strict_types=1);

namespace Elements\Store\Models;

use Kodelines\Abstract\Model;
use Elements\Store\Helpers\Price;

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

    $language = !empty($filters['language']) ? $filters['language'] : language();

    if(user('id_stores')) {
      $id_stores = user('id_stores');
    } else {
      $id_stores = config('store','id_stores');
    }

    
    $query = "SELECT 
      store_products.*,
      products.*,
      brands.title AS brand,
      store_products_availability.availability_b2b AS availabilty,
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
        "products_lang.*
        FROM store_products
          JOIN products ON store_products.id_products = products.id
          JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = ".encode($language)."
          JOIN store_products_availability ON store_products_availability.id_store_products = store_products.id
          JOIN store_products_prices ON store_products_prices.id_store_products = store_products.id AND store_products_prices.id_stores = ". id($id_stores) ."
          LEFT JOIN brands ON brands.id = products.id_brands
          LEFT JOIN store_products_discounts ON store_products_discounts.id_store_products = store_products.id AND imported = 1
        WHERE store_products.status = 'on_sale' ";

      if(!empty($filters['id_categories'])) {
        $query .= " AND store_products.id_products IN (SELECT id_products FROM products_categories WHERE products_categories.id_categories = ".encode($filters['id_categories']). ")";
      }
      
      $query .= $this->applyFilters($filters); 

      return $query;

  }


    /**
   * Get a Single row by id 
   *
   * @method get
   * @param  $id    id elemento
   * @return array|false
   */
  public function get(int $id, $filters = []): array|false
  {
    if(!$data = parent::get($id,$filters)) {
      return false;
    }
    
    return array_merge($data,Price::calculate($data));

  }



  /**
   * Get a Single row by slug (only for tables with _lang)
   *
   * @method get
   * @param  $slug   slug elemento
   * @return array|false
   */
  public function slug(string $slug = '', $filters = []): array|false
  {

    if(!$data = parent::slug($slug,$filters)) {
      return false;
    }
    
    return array_merge($data,Price::calculate($data));
    
  }


  /**
   * Ritorna lista di occorrenze con filtri applicabili a query principale
   *
   * @param  array $filters
   * @return array
   */
  public function list($filters = []):array
  {

    $data = [];

    foreach( parent::list($filters) as $key => $value) {
      $data[$key] = array_merge($value,Price::calculate($value));
    }

    return $data;

  }


}

?>