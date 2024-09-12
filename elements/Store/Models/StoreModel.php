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

  /**
   * Setto default con le impostazioni dal constructor
   */
  public function __construct()
  {
    parent::__construct();

    /**
     * Campi predefiniti
     */
    $this->defaults = [];

    //Setto prezzi richiesti in base a tipi abilitati su store
    if(config('store','enable_b2c') == true) {
      $this->validator['price_taxes_excluded_b2c'] = ['required'];
    }

    if(config('store','enable_b2b') == true) {
        $this->validator['price_taxes_excluded_b2b'] = ['required']; 
    }

    if(config('store','enable_horeca') == true) {
        $this->validator['price_taxes_excluded_horeca'] = ['required']; 
    }

    //Se settato id reseller il campo id_resellers è predefinito
    if(defined('_ID_RESELLERS_')) {
      $this->defaults['id_resellers'] = _ID_RESELLERS_;
    }


  }

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
      store_products_availability.availability_b2b AS availabilty,
      store_products_prices.price,
      store_products_discounts.discount_offer_percentage,";

      if(user()) {
        $query .= "      
        ".user('discount_client_percentage')." AS discount_client_percentage,
        ".user('discount_contract_percentage')." AS discount_contract_percentage,
        ".user('discount_final_percentage')." AS discount_final_percentage,";
      } else {
        $query .= "      
          NULL AS discount_client_percentage,
          NULL AS discount_contract_percentage,
          NULL AS discount_final_percentage,";
      }

    $query = "
          products_lang.*
        FROM store_products
          JOIN products ON store_products.id_products = products.id
          JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = ".$language."
          JOIN store_products_availability ON store_products_availability.id_store_products = store_products.id
          JOIN store_products_prices ON store_products_prices.id_store_products = store_products.id AND store_products_prices.id_stores = ". id($id_stores) ."
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

    foreach( parent::list($filters) as $key => $value) {
      $data[$key] = array_merge($value,Price::calculate($value));
    }

    return $data;

  }


}

?>