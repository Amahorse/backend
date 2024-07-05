<?php

declare(strict_types=1);

namespace Elements\Store\Models;

use Kodelines\Abstract\Model;
use Elements\Resellers\Resellers;

class WarehouseModel extends Model {

  public $table = 'store_products';
  
  public $documents = 'products';

  public $uploads = ['cover','retro','merchant_cover'];

  public $childs = ['components','discounts','landings','resellers'];

  public $view = 'product';

  public $meta = [
    'ld_type' => 'Product',
    'opengraph_type' => 'product'
  ];


  public $validator = [
    'status' => ['required'],
    'timing_supply' => ['required']
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
      'id_countries' => config('default','id_countries'),
      'listing' => 'normal',
      'format' => 'single',
      'status' => 'on_sale',
      'weight' => 0,
      'visibility' => 'published',
      'count_on_shipping' => 1,
      'timing_supply' => 0,
      'rules_configurator_price' => 1,
      'rules_configurator_availability' => 1,
      'id_products' => null,
      'available_b2c' => 1,
      'available_b2b' => 1,
      'available_horeca' => 1,
      'available_components' => 1,
      'maximum_order' => null,
      'minimum_order_b2c' => 1,
      'minimum_order_b2b' => 1,
      'minimum_order_horeca' => 1,
      'multiples_available_b2c' => 1,
      'multiples_available_b2b' => 1,
      'multiples_available_horeca' => 1
    ];

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

    
    $query = "SELECT 
      store_products.*,
      store_products.id AS id_store_products,
      store_products_lang.*,
      products.type,
      categories_lang.title AS category,
      CASE WHEN store_products_lang.title IS NOT NULL THEN store_products_lang.title ELSE products.name END AS name
      FROM 
        store_products 
      LEFT JOIN store_products_lang ON store_products.id = store_products_lang.id_store_products AND store_products_lang.language = ".encode($language)." 
      LEFT JOIN products ON store_products.id_products = products.id
      LEFT JOIN categories c ON c.id = store_products.id_categories
      LEFT JOIN categories_lang ON c.id = categories_lang.id_categories AND categories_lang.language = " . encode($language) . " 
      WHERE store_products.id IS NOT NULL ";

      $filters = Resellers::addFilters($filters);

      $query .= $this->applyFilters($filters); 

      return $query;

  }


}

?>