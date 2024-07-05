<?php

declare(strict_types=1);

namespace Elements\Orders\Models;

use Kodelines\Abstract\Model;
use Elements\Store\Warehouse;
use Elements\Shipping\Shipping;

class ProductsModel extends Model {

  public $table = 'store_orders_products';

  public $defaults = [
    'purchase_type' => 'single',
    'format' => 'single',
    'forced' => 0,
    'minimum_order' => null,
    'maximum_order' => null,
    'shipping_delay' => 0
  ];

  /**
   * Generates the query string for retrieving products with optional filters
   *
   * @param array $filters
   * @return string
   */
  public function query($filters = []):string { 

    // Generic query
    $query = "
    SELECT
       store_orders_products.*,";

       if(isset($filters['group_order'])) {
         $query .= "
          SUM(store_orders_products.quantity) AS total_quantity,
          GROUP_CONCAT(store_orders.number, '') AS orders,
          ";
       }

      $query .= "
      labels.type AS label_type,
      labels.file_type AS label_file_type,
      labels.preview AS label_preview,
      labels.file AS label_file,
      labels.settings AS label_settings,
      labels.uuid AS label_uuid,
      labels.id_templates AS id_templates_label,
      CASE WHEN configurator.id IS NOT NULL AND (store.id IS NULL OR store.rules_configurator_availability = 1) THEN configurator_warehouse_availability.availability_warehouse ELSE store.availability_warehouse END AS availability_warehouse,
      CASE WHEN configurator.id IS NOT NULL AND (store.id IS NULL OR store.rules_configurator_availability = 1) THEN configurator_warehouse_availability.availability_virtual ELSE store.availability_virtual END AS availability_virtual,
      CASE WHEN configurator.weight IS NULL THEN store.weight ELSE configurator.weight END AS weight,
      CASE WHEN configurator.shelf_location IS NULL THEN store.shelf_location ELSE configurator.shelf_location END AS shelf_location,
      CASE WHEN configurator.status IS NOT NULL THEN configurator.status ELSE store.status END AS status,
      CASE 
        WHEN store_orders.type = 'b2c' AND store.minimum_order_b2c IS NOT NULL THEN store.minimum_order_b2c   
        WHEN store_orders.type = 'b2b' AND store.minimum_order_b2c IS NOT NULL THEN store.minimum_order_b2b   
        WHEN store_orders.type = 'horeca' AND store.minimum_order_horeca IS NOT NULL THEN store.minimum_order_horeca     
      ELSE configurator.minimum_order END AS minimum_order,
      CASE WHEN configurator.maximum_order IS NOT NULL THEN configurator.maximum_order ELSE store.maximum_order END AS maximum_order,
      CASE WHEN configurator.manufacturer IS NOT NULL THEN configurator.manufacturer ELSE manufacturers.name END AS manufacturer,
      CASE WHEN store.cover IS NOT NULL THEN store.cover ELSE products.cover END AS cover,
      CASE WHEN pay.status IS NULL THEN 'no' ELSE pay.status END AS payment_status,
      printing_front.technique AS front_technique,
      printing_front.paper AS front_paper,
      printing_front.paper_name AS front_paper_name,
      printing_front.paper_characteristic AS front_paper_characteristic,
      printing_front.paper_type AS front_paper_type,
      printing_front.cut AS front_cut,
      printing_front.width AS front_label_width,
      printing_front.height AS front_label_height,
      printing_back.technique AS back_technique,
      printing_back.paper AS back_paper,
      printing_back.paper_name AS back_paper_name,
      printing_back.paper_characteristic AS back_paper_characteristic,
      printing_back.paper_type AS back_paper_type,
      printing_back.cut AS back_cut,
      printing_back.width AS back_label_width,
      printing_back.height AS back_label_height,
      CASE 
       WHEN store_lang.title IS NOT NULL THEN store_lang.title 
       WHEN products_lang.title IS NOT NULL THEN products_lang.title
       WHEN products.name IS NOT NULL THEN products.name
      ELSE store_orders_products.title END AS title,
      store_lang.description,
      store_lang.slug,
      store.rules_configurator_availability,
      products.name_standard_label,
      products.name,
      products.export_code,
      configurator.id_configurator_containers,
      configurator.id_configurator_warehouse,
      configurator.image_full,
      configurator.capsule_type,
      configurator.capsule_material,
      CASE WHEN configurator.capsule_color IS NOT NULL THEN configurator.capsule_color ELSE 'none' END AS capsule_color,
      configurator.container_title,
      configurator.container_capacity,
      configurator.container_unit,
      configurator.container_material,
      configurator.container_size,
      configurator.plug,
      configurator.retro_pdf_white,
      configurator.retro_pdf_black,
      shipping.date_delivery,
      shipping.date_delivery_max,
      shipping.date_retire,";

      if(!empty($filters['report'])) {
        $query .= "
        store_orders_products_lotto.lotto,
        store_orders_products_lotto.quantity AS lotto_quantity,
        store_tax.description AS tax_description,
        store_tax.description_short AS tax_description_short,
        store_tax.natura AS tax_natura,";
        
      }

      $query .= "
      store_orders.number AS order_number,
      store_orders.id_users,
      store_orders.status AS order_status,
      store_orders.type AS client_type,
      store_orders.date_order
  FROM store_orders_products
     JOIN store_orders ON store_orders.id = store_orders_products.id_store_orders
     LEFT JOIN products ON store_orders_products.id_products = products.id
     LEFT JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = " . encode(language()) . "
     LEFT JOIN manufacturers ON products.id_manufacturers = manufacturers.id
     LEFT JOIN shipping ON shipping.id = store_orders_products.id_shipping
     LEFT JOIN labels ON store_orders_products.id_labels = labels.id
     LEFT JOIN configurator ON configurator.id_configurator_warehouse_availability = store_orders_products.id_configurator AND configurator.client_type = store_orders.type
     LEFT JOIN configurator_warehouse_availability ON configurator.id = configurator_warehouse_availability.id
     LEFT JOIN printing printing_front ON printing_front.id = store_orders_products.id_printing_front
     LEFT JOIN printing printing_back ON printing_back.id = store_orders_products.id_printing_back
     LEFT JOIN store_products store ON store.id = store_orders_products.id_store_products
     LEFT JOIN store_products_lang store_lang ON store.id = store_lang.id_store_products AND store_lang.language = ".encode(language())."
     LEFT JOIN payments_incoming pay ON pay.id_store_orders = store_orders.id";

     if(!empty($filters['report'])) {
      
      $query .= " 
        LEFT JOIN store_orders_products_lotto ON store_orders_products_lotto.id_store_orders_products = store_orders_products.id 
        LEFT JOIN store_tax ON store_tax.id = store_orders_products.id_store_tax";
     }

     $query .= "
     WHERE store_orders_products.id_store_orders IS NOT NULL";

  // Filters for order date
  if(!empty($filters['date_order_from'])) {
    $query .= " AND store_orders.date_order >= " . encode($filters['date_order_from']);
  }

  if(!empty($filters['date_order_to'])) {
    $query .= " AND store_orders.date_order <= " . encode($filters['date_order_to']);
  }

  // Filters for delivery date
  if(!empty($filters['date_delivery_from'])) {
    $query .= " AND DATE(shipping.date_delivery) >= " . encode($filters['date_delivery_from']);
  }

  if(!empty($filters['date_delivery_to'])) {
    $query .= " AND DATE(shipping.date_delivery) <= " . encode($filters['date_delivery_to']);
  }

  // Filters for retire date
  if(!empty($filters['date_retire_from'])) {
    $query .= " AND DATE(shipping.date_retire) >= " . encode($filters['date_retire_from']);
  }
  
  if(!empty($filters['date_retire_to'])) {
    $query .= " AND DATE(shipping.date_retire) <= " . encode($filters['date_retire_to']);
  }

  // Filter for shipping processing
  if(!empty($filters['shipping_processing'])) {
    $query .= " AND shipping.processing = " . encode($filters['shipping_processing']);
  }

  // Filter for manufacturer ID
  if(!empty($filters['id_manufacturers'])) {
    $query .= " AND products.id_manufacturers = " . id($filters['id_manufacturers']);
  }

  if(!empty($filters['id_resellers'])) {
    $query .= " AND store_orders.id_resellers = " . id($filters['id_resellers']);
  }

  // Filter for payment status
  if(!empty($filters['payment_status'])) {
    if($filters['payment_status'] == 'no') {
      $query .= " AND (pay.status = 'no' OR pay.status IS NULL) ";
    } else {
      $query .= " AND pay.status = " . encode($filters['payment_status']);
    }
  }

  // Filter for shipping
  if(!empty($filters['shipping'])) {
    // TODO: Remove this rule
    //$query .= " AND (pay.status = 'yes')";
  }

  // Filter for production
  if(!empty($filters['production'])) {
    // TODO: Remove this rule
    //$query .= " AND (pay.status IS NOT NULL AND pay.status <> 'no')";
  }

  // Filter for order status
  if(!empty($filters['order_status'])) {

    if($filters['order_status'] == 'completed_confirmed') {
      $query .= " AND (store_orders.status = 'completed' OR store_orders.status = 'confirmed')";
    } else {
      $query .= " AND store_orders.status = " . encode($filters['order_status']);
    }

  
  }

  // Filter for order type
  if(!empty($filters['order_type'])) {
    $query .= " AND store_orders.type = " . encode($filters['order_type']);
  }

  if(empty($filters['groupby'])) {
    $filters['groupby'] = "store_orders_products.id";
  }

  $query .= $this->applyFilters($filters);

  return $query;
}

/**
 * Retrieves the full list of products with optional filters
 *
 * @param array $filters
 * @return array
 */
public function fullList($filters = []):array {

  $filters['id_store_orders_products_parent'] = NULL;

  $list = [];

  $products = $this->list($filters);

  foreach($products as $product) {
    
    $product['components'] = $this->list(['id_store_orders_products_parent' => $product['id']]);

    $list[] = $this->generate($product);
  }

  return $list;
}

/**
 * Retrieves a single product with its components
 *
 * @param integer $id
 * @return array
 */
public function fullGet(int $id, $filters = []):array|false {

  $product = $this->get($id,$filters);
    
  $product['components'] = $this->list(['id_store_orders_products_parent' => $product['id']]);

  return $this->generate($product);
}
  
/**
 * Fixes fields for display, checks, and other operations for each product
 *
 * @param array $product
 * @return array
 */
public function generate(array $product):array {

    //Anche se c'è join con spedizione se l'ordine non è completato la data di ritiro viene calcolata sempre se l'ordine non è completato o confermato
    if($product['order_status'] !== 'completed' || $product['order_status'] !== 'confirmed') {
      $product['date_retire'] = null;
      $product['date_delivery_max'] = null;
      $product['date_delivery'] = null;
    } 


    $product['availability_max'] = Warehouse::getAvailabilityMax($product);

    if($product['quantity'] > $product['availability_max'] || $product['status'] <> 'on_sale') {
      $product['payable'] = 0;
    } else {
      $product['payable'] = 1;
    }

    return $product;
}

}

?>