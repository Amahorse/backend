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
	store_products_availability.availability_b2b AS availabilty,
	store_products_prices.price,
	store_products_discounts.discount_offer_percentage,
	NULL AS discount_client_percentage,
	NULL AS discount_contract_percentage,
	NULL AS discount_final_percentage,
	products_lang.*
 FROM store_products
	JOIN products ON store_products.id_products = products.id
	JOIN store_products_availability ON store_products_availability.id_store_products = store_products.id
	JOIN store_products_prices ON store_products_prices.id_store_products = store_products.id AND store_products_prices.id_stores = 1
	JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = 'it'
	LEFT JOIN store_products_discounts ON store_products_discounts.id_store_products = store_products.id AND imported = 1
 WHERE store_products.status = 'on_sale' AND store_products.id_products IN (SELECT id_products FROM products_categories WHERE products_categories.id_categories = 2073)
	;";


      $query .= $this->applyFilters($filters); 

      return $query;

  }


}

?>