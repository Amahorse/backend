<?php

declare(strict_types=1);

namespace Elements\Store;

use Kodelines\Log;
use Kodelines\Db;
USE Elements\Configurator\Helpers\Calculator;
use Elements\Store\Models\WarehouseModel;
use Elements\Store\Helpers\Package;



/**
 * La classe store può avere una istanza signleton globale per tutte le chiamate istanziata di default con parametri base
 * Se si instanzia una nuova classe store vanno settati a mano tutti i parametri
 */

class Store  {

  use \Elements\Store\Traits\StoreTrait;

  /**
   * Il calculator è un istanza del configuratore che estende questa classe, va clonata
   *
   * @var object
   */
  public Calculator $configurator;
  

  /**
   * Il modello è warehouse
   *
   * @var object
   */
  public WarehouseModel $model;

  /**
   * Var to containe singleton instance
   *
   * @var object
   */
  protected static $instance = null;

  /**
   * Get singleton class instance
   *
   * @method getInstance
   * @return object      return var $instance
   */
  public static function getInstance() {   if(self::$instance == null) {self::$instance = new Store;} return self::$instance;}


  
  /**
   * Il constructor instanzia anche nuova classe configuratore
   *
   */
  public function __construct() {

    //Chiamo funzione inizializzazione del trait
    $this->init();

    //Il modello lo instanzio manualmente perchè questa classe ha stessa tabella di prodotti ma fa cose diverse
    $this->model = new WarehouseModel;

  
  }


  /**
   * Questa è una funzione diversa dalle altre query in quanto prende tabelle dinamicamente in base a paramentri e non ha filtri predefiniti
   *
   * @param array $filters
   * @return string
   */
  public function query(array $filters = []):string 
  {


    if($this->id_countries == NULL) {
      $country_selection = " IS NULL ";
    } else {
      $country_selection = " = " . $this->id_countries;
    }

    if(empty($this->id_store_tax)) {
      $tax_selection = "LEFT JOIN store_tax tax ON tax.id = (SELECT id FROM store_tax WHERE FIND_IN_SET(store_products.taxation_type,store_tax.taxation_type) AND FIND_IN_SET(".encode($this->type).",client_type) AND id_countries ".$country_selection . " ORDER BY main DESC LIMIT 1)";
    } else {
      $tax_selection = "LEFT JOIN store_tax tax ON tax.id = " . $this->id_store_tax;
    }

  
    if(!empty($filters['language'])) {
      $this->language = $filters['language'];
    } 

    if(!empty($filters['id_stores'])) {
      $this->setStore($filters['id_stores']);
    }

    //Amministratore su pannello può bypassare il minimo ordine
    $quantity_selection = "CASE WHEN (store_products.minimum_order_".$this->type." IS NOT NULL AND store_products.minimum_order_".$this->type." > CAST(". $this->quantity ." AS INT)) THEN store_products.minimum_order_".$this->type." ELSE CAST(". $this->quantity ." AS INT) END";
    



    //DA VEDERE 
    //weight (solo se rules_configurator_availability = 1 o sempre)?

    $query = "
      SELECT
      store_products.id,
      store_products.id_configurator,
      store_products.id_products,
      store_products.id AS id_store_products,
      store_products.id_products,
      store_products.id_printing_back,
      store_products.id_printing_front,
      store_products.id_labels,
      store_products.id_categories,
      store_products.id_templates,
      store_products.listing,
      store_products.status,
      store_products.code,
      store_products.format,
      COALESCE(configurator.container_size,store_products.size) AS size,
      store_products.visibility,
      store_products.availability_warehouse,
      store_products.availability_virtual,
      store_products.count_on_shipping,
      COALESCE(configurator.container_weight_full,store_products.weight) AS weight,
      store_products.rules_configurator_price,
      store_products.rules_configurator_availability,
      store_products_lang.description,
      store_products_lang.content,
      store_products_lang.meta_title,
      store_products_lang.meta_description,
      store_products_lang.merchant,
      store_products_lang.merchant_title,
      store_products_lang.merchant_description,
      store_products_lang.slug,
      store_products_lang.indexable,
      store_products_lang.title_image,
      store_products.price_taxes_excluded_".$this->type." AS price_taxes_excluded,
      store_products.minimum_order_".$this->type." AS minimum_order,
      store_products.maximum_order,
      store_products.multiples_available_".$this->type." AS multiples_available,
      COALESCE(store_products.cover,products.cover) AS cover,
      COALESCE(store_products.retro,products.retro) AS retro,
      COALESCE(store_products_lang.title,products_lang.title) AS title,
      store_products.merchant_google_category_id,
      categories.icon_png AS category_icon_png,
      categories.icon_svg AS category_icon_svg,
      categories.evidence,
      categories_lang.title AS category,
      categories_lang.slug AS category_slug,
      COALESCE(products.type,'other') AS type,
      products.labels,
      products.region,
      products.type_data,
      products.name_standard_label,
      products_lang.taste_notes,
      products_lang.taste_smell,
      products_lang.taste_palate,
      products_lang.taste_color,
      products_lang.pairing,
      tax.id AS id_store_tax,
      tax.tax,
      COALESCE(configurator.free_port,store_products.free_port) AS free_port,
      configurator.id_products,
      configurator.id_configurator_warehouse,
      configurator.id_configurator_containers,
      configurator.id_configurator_capsules_images,
      configurator.only_front_label,
      configurator.packaging,
      configurator.year,
      configurator.typology,
      configurator.show_manufacturer,
      configurator.capsule_color,
      configurator.capsule_image,
      configurator.container_title,
      configurator.container_capacity,
      configurator.container_conversion_rate,
      configurator.container_size,
      configurator.container_shape,
      configurator.container_height,
      configurator.container_width,
      configurator.container_depth,
      configurator.container_circumference,
      configurator.container_weight_full,
      configurator.image_full,
      configurator.show_name_standard_label,
      COALESCE(prices.timing_supply,store_products.timing_supply) AS timing_supply,
      COALESCE(prices.price_buy,store_products.price_buy) AS price_buy,
      prices.price_processing,
      prices.price_shipping_adjustment,
      prices.price_packaging,
      prices.price_free_port,
      prices.price_front_label,
      prices.price_retro_label,
      prices.processing_included,
      prices.label_included,
      prices.price_recharge,
      prices.price_recharge_percentage,
      printing_front.width AS front_label_width,
      printing_front.height AS front_label_height,
      printing_front.paper AS front_paper,
      printing_front.cut AS front_cut,
      printing_back.width AS back_label_width,
      printing_back.height AS back_label_height,
      printing_back.paper AS back_paper,
      printing_back.cut AS back_cut,
      ".$quantity_selection." AS quantity,
      countries.iso AS country,
      CASE 
        WHEN discounts_product.date_end IS NOT NULL AND discounts_global.date_end IS NOT NULL AND DATE(discounts_global.date_end) > DATE(discounts_product.date_end) THEN discounts_product.date_end
        WHEN discounts_product.date_end IS NOT NULL AND discounts_global.date_end IS NOT NULL AND DATE(discounts_global.date_end) < DATE(discounts_product.date_end) THEN discounts_global.date_end
        WHEN discounts_product.date_end IS NOT NULL THEN discounts_product.date_end
      ELSE discounts_global.date_end END AS discounts_end,
      discounts_global.discount_percentage AS discounts_global_percentage,
      discounts_product.discount_percentage AS discounts_product_percentage";

      if(!empty($this->id_stores)) {
        $query .=  ", stores.id AS id_stores,
        stores.shipping_delay,
        stores.shipping_max_hour,
        stores.price_recharge_percentage AS price_store_recharge_percentage";
      } else {
        $query .=  ", NULL AS id_stores,
        ".config('store','shipping_delay')." AS shipping_delay,
        ".encode(config('store','shipping_max_hour'))." AS shipping_max_hour,
        0 AS price_store_recharge_percentage";
      }
    
      //TODO: rimuovere quando c'è nuovo front end
      $query .=  ", 0 AS in_cart";

      /* RIPRISTINARE INSIEME A JOIN SOTTO CASO SERVANO DATI SU QUANTI PRODOTTI CI SONO NEL CARRELLO O COSE VARIE
      if(!empty($this->id_store_orders)) {
        $query .=  ", CASE WHEN cart.quantity IS NULL THEN 0 ELSE cart.quantity END AS in_cart,
                      cart.comment";
      } else {
        $query .=  ", 0 AS in_cart,
                     NULL AS comment";
      }
      */

      if(!empty($this->id_resellers)) {
        $query .= "
        , configurator_resellers_shipping_adjustment.price_reseller_shipping_adjustment AS price_reseller_shipping_adjustment_endchain
        , resellers.id AS id_resellers
        , resellers_recharges.percentage AS price_reseller_recharge_percentage
        , resellers.price_reseller_marketing_percentage
        , store_products_resellers.price_recharge_percentage AS price_reseller_recharge_percentage_product
        , resellers_shipping_adjustment.price_reseller_shipping_adjustment ";
      } else {
        $query .= "
          , store_products.id_resellers
          , 0 AS price_reseller_recharge_percentage
          , 0 AS price_reseller_marketing_percentage
          , 0 AS price_reseller_shipping_adjustment
          , 0 AS price_reseller_shipping_adjustment_endchain
          , 0 AS price_reseller_recharge_percentage_product";
      }



      if(!empty($filters['landing']) || !empty($filters['landing_v2']))  {    
        $query .= ", store_products_landings.position ";
        $query .= ", store_products_landings.priority ";
      } else {
        $query .= ", NULL AS position";
        $query .= ", NULL AS priority";
      }
  


    $query .= "
    FROM store_products 
      LEFT JOIN store_products_lang ON store_products_lang.id_store_products = store_products.id AND store_products_lang.language = " . encode($this->language) . "
      ".$tax_selection."
      LEFT JOIN products ON products.id = store_products.id_products
      LEFT JOIN products_lang ON products_lang.id_products = products.id AND products_lang.language = " . encode($this->language) . "
      LEFT JOIN configurator ON store_products.id_configurator = configurator.id AND configurator.client_type = ".encode($this->type). "
      LEFT JOIN configurator_prices_".$this->type." prices ON prices.id_configurator = configurator.id AND (". $quantity_selection ." BETWEEN prices.quantity_min AND (CASE WHEN prices.quantity_max IS NULL THEN 1000000 ELSE prices.quantity_max END))  
      LEFT JOIN store_discounts discounts_global ON FIND_IN_SET(".encode($this->type).",discounts_global.client_type)  AND discounts_global.date_end >= DATE(NOW()) AND discounts_global.date_start <= DATE(NOW()) AND discounts_global.status = 1 AND discounts_global.type = 'fixed'
      LEFT JOIN store_products_discounts discounts_product ON
        FIND_IN_SET(".encode($this->type).",discounts_product.client_type) AND (discounts_product.id_countries ".$country_selection." OR discounts_product.id_countries IS NULL)
        AND (discounts_product.date_end >= DATE(NOW()) OR discounts_product.date_end IS NULL)
        AND (discounts_product.date_start <= DATE(NOW()) OR discounts_product.date_start IS NULL)
        AND (".$quantity_selection." BETWEEN discounts_product.quantity_min AND (CASE WHEN discounts_product.quantity_max IS NULL THEN 1000000 ELSE discounts_product.quantity_max END))
        AND discounts_product.id_store_products = store_products.id
      LEFT JOIN countries ON products.id_countries = countries.id
      LEFT JOIN printing printing_front ON printing_front.id = store_products.id_printing_front
      LEFT JOIN printing printing_back ON printing_back.id = store_products.id_printing_back
      LEFT JOIN categories ON categories.id = store_products.id_categories
      LEFT JOIN categories_lang categories_lang ON categories.id = categories_lang.id_categories AND categories_lang.language = " . encode($this->language);

      /* RIPRISTINARE IN CASO SERVANO DATI SU QUANTI PRODOTTI CI SONO NEL CARRELLO O COSE VARIE
      if(!empty($this->id_store_orders)) {
        $query .= " 
        LEFT JOIN store_orders ON store_orders.id = " . id($this->id_store_orders) . "
        LEFT JOIN store_orders_products cart ON cart.id_store_products = store_products.id AND cart.id_store_orders = " . id($this->id_store_orders);
      }
      */
 

      
      if(!empty($filters['category_status'])) {
        $query.= " AND (categories.status IS NULL OR categories.status = " . encode($filters['category_status']) . ")";
      }



      if(!empty($this->id_stores)) {
        $query .= " JOIN stores ON stores.id = " . id($this->id_stores);
      }

      if(!empty($this->id_resellers)) {
        $query .= " 
          JOIN resellers ON resellers.id = ".id($this->id_resellers)." 
          LEFT JOIN store_products_resellers ON store_products_resellers.id_store_products = store_products.id 
          LEFT JOIN resellers_recharges ON resellers_recharges.id_resellers = ".id($this->id_resellers)." AND FIND_IN_SET(".encode($this->type).",resellers_recharges.type) AND (". $quantity_selection ." BETWEEN resellers_recharges.quantity_min AND (CASE WHEN resellers_recharges.quantity_max IS NULL THEN 1000000 ELSE resellers_recharges.quantity_max END))
          LEFT JOIN resellers_shipping_adjustment ON resellers_shipping_adjustment.id_resellers = ".id($this->id_resellers)." 
            AND FIND_IN_SET(".encode($this->type).",resellers_shipping_adjustment.type) 
            AND (FIND_IN_SET(configurator.container_capacity, resellers_shipping_adjustment.capacity) OR (resellers_shipping_adjustment.capacity IS NULL AND configurator.container_capacity IS NULL)) 
            AND FIND_IN_SET(products.type, resellers_shipping_adjustment.product_type)
            AND ((". $quantity_selection ." * COALESCE(configurator.container_weight_full,store_products.weight)) BETWEEN resellers_shipping_adjustment.weight_min AND (CASE WHEN resellers_shipping_adjustment.weight_max IS NULL THEN 1000000 ELSE resellers_shipping_adjustment.weight_max END))
          LEFT JOIN configurator_resellers_shipping_adjustment ON configurator_resellers_shipping_adjustment.id_resellers = ".id($this->id_resellers)." 
            AND FIND_IN_SET(".encode($this->type).",configurator_resellers_shipping_adjustment.type) AND (". $quantity_selection ." BETWEEN configurator_resellers_shipping_adjustment.quantity_min 
            AND (CASE WHEN configurator_resellers_shipping_adjustment.quantity_max IS NULL THEN 1000000 ELSE configurator_resellers_shipping_adjustment.quantity_max END))
            AND FIND_IN_SET(configurator.container_capacity, configurator_resellers_shipping_adjustment.capacity) 
            AND FIND_IN_SET(products.type, configurator_resellers_shipping_adjustment.product_type)";
      }

      if(!empty($filters['landing']))  {
       
        $query .= " JOIN store_products_landings ON store_products_landings.id_store_products = store_products.id AND store_products_landings.landing = " . encode($filters['landing']);
      
        if(!empty($filters['position']))  {
          $query .= " AND FIND_IN_SET(store_products_landings.position," . encode($filters['position']) . ")";
        }

      }

      if(!empty($filters['landing_v2']))  {
       
        $query .= " JOIN store_products_landings ON store_products_landings.id_store_products = store_products.id AND store_products_landings.landing_v2 = " . encode($filters['landing_v2']);
      
        if(!empty($filters['position']))  {
          $query .= " AND FIND_IN_SET(store_products_landings.position," . encode($filters['position']) . ")";
        }

      }


    $query .= " WHERE store_products.id IS NOT NULL ";


    if(!empty($filters['id_categories'])) {

      $query .= " AND (
        (store_products.id_categories = ".id($filters['id_categories']).") OR 
        (store_products.id_categories IN 
          (SELECT categories_sub.id FROM categories categories_sub WHERE categories_sub.id_categories_main = ".id($filters['id_categories']); 

          if(!empty($filters['category_status'])) {
            $query.= " AND (categories_sub.status IS NULL OR categories_sub.status = " . encode($filters['category_status']) . ")";
          }
          
        $query .=")))"; 

      unset($filters['id_categories']);
    }

   

     //Id reseller definito su classe setta il filtro automaticamente
     if(!empty($this->id_resellers)) {

       $query .= " AND (store_products.id_resellers = " . id($this->id_resellers) . " OR store_products_resellers.id_resellers = ".id($this->id_resellers).")";

      } 

     if(isset($filters['available'])) {

      // amministratore vede tutti i prodotti maggiori di 0 indipendentemente dal minimo ordine
      if(auth('administrator',true)) {
        $query .= " AND (store_products.availability_warehouse > 0 OR store_products.availability_warehouse IS NULL OR store_products.availability_virtual > 0 OR store_products.availability_virtual IS NULL) " ;
      } else {
        $query .= " AND (store_products.availability_warehouse >= store_products.minimum_order_".$this->type." OR store_products.availability_warehouse IS NULL OR store_products.availability_virtual >= store_products.minimum_order_".$this->type." OR store_products.availability_virtual IS NULL) " ;
      }

     }
     
     if(isset($filters['available_virtual'])) {
      $query .= " AND (store_products.availability_virtual > 0 OR store_products.availability_virtual IS NULL) " ;
     }

     if(isset($filters['available_warehouse'])) {
      $query .= " AND (store_products.availability_warehouse > 0 OR store_products.availability_warehouse IS NULL) " ;
     }

     if(!empty($filters['client_available']) && empty($this->id_resellers)) {
      $query .= " AND store_products.available_".$this->type." = 1 " ;
     }
     
    //Search su campi titolo e categoria
    if(isset($filters['search'])) {

      $query .= " AND (store_products_lang.title LIKE '%".$filters['search']."%' OR categories_lang.title LIKE '%".$filters['search']."%')";
        
      unset($filters['search']);
      
    }

    //Size fa doppio controllo su prodotti configuratore o prodotti store
    if(!empty($filters['size'])) {

      $query .= " AND ((store_products.id_configurator IS NOT NULL AND configurator.container_size = " . encode($filters['size']) .") OR (store_products.id_configurator IS NULL AND store_products.size = " . encode($filters['size']) .")) ";
      
      unset($filters['size']);

    }


     //Filtri su tabella prodotto
     if(!empty($filters['type'])) {
       $query .= " AND products.type = " . encode($filters['type']);
     }

     if(!empty($filters['id_countries'])) {
      $query .= " AND products.id_countries = " . id($filters['id_countries']);
     }

     if(!empty($filters['organolectic_macroclassification'])) {
      $query .= " AND products.organolectic_macroclassification IS NOT NULL AND FIND_IN_SET(".encode($filters['organolectic_macroclassification']).",products.organolectic_macroclassification) ";
    }

    if(!empty($filters['typology'])) {
      $query .= " AND configurator.typology = " . encode($filters['typology']);
    }

    if(!empty($filters['exclude'])) {
      $query .= " AND store_products.id <> " . id($filters['exclude']);
    }

     //Controllo se box è abbinato a contenitore
     if(!empty($filters['id_configurator_containers'])) {
      $query .= " AND store_products.id_products IN (SELECT id_products FROM configurator_containers_boxes WHERE id_configurator_containers = ".id($filters['id_configurator_containers']).") ";
     }

     //Controllo se stampa è abbinata a template 
     if(!empty($filters['type']) && $filters['type'] == 'printing' && !empty($filters['id_templates'])) {

      $query .= " AND store_products.id IN (SELECT id_store_products FROM templates_store_products WHERE id_templates = ".id($filters['id_templates']).") ";

      //Tolgo filtro id_templates diretto altrimenti il sistema cerca prodotto template
      unset($filters['id_templates']);
     }
     
     $query .= $this->model->applyFilters($filters); 

     return $query;
    
  }




  /**
   * Questa serve per la sitemap
   *
   * @return object
   */
  public static function sitemap($filters = []) {  
    return self::getInstance()->list($filters);
  }


  /**
   * Genera i prezzi e fa un fix dei valori del prodotto prima ti passarli ad array di ritorno
   *
   * @param array $product
   * @return array
   */
  public function generate(array $product, $price = true):array|bool {

    $product = $this->generateBaseFields($product);

    //Calcolo tutte le variabili prezzo del prodotto in base a valori recuperati dal database
    if($price == true) {

      //LISTINO CONFIGURATORE RECUPERO IL PREZZO IVA ESCLUSA TRAMITE L'ENDCHAIN DEL CONFIGURATORE
      if($product['listing'] == 'configurator_preset' && isset($product['rules_configurator_price']) && $product['rules_configurator_price'] == 1) {
 
        $product = array_merge($product,$this->configurator->endChain($product));
       
      } 

      $product = array_merge($product,$this->price->calculate($product,$product['quantity']));
      
    }


    //COMPONENTI PACCHETTO IN CASO DI PRODOTTO FORMATO PACCHETTO O LISTINO PACCHETTI
    if($product['format'] == 'package' || $product['listing'] == 'packages') {

      $product['components'] = Package::getComponents(id($product['id']));

      if(count($product['components']) == 0) { 

        new Log('sync','package_components_not_found',$product);

        return false;

      }

      $product = Package::getValues($product);

    } else {

      $product['components'] = [];
    }

    return $product;

  }


  /**
   * Funzione statica che fa query e calcola prezzi massivi dei prodotti in base a parametri
   *
   * @param array $filters
   * @return array
   */
  public function list(array $filters = []):array {

    $data = [];

    if(!$products = Db::getArray($this->query($filters))) {
      return $data;
    }

    foreach($products as $product) {

      if($generated = $this->generate($product)) {
        $data[] = $generated;
      }
 
    }

    return $data;

  }

  /**
   * Prende un solo prodotto
   *
   * @param array       $filters
   * @return array
   */
  public function get(int $id, $price = true):array|bool {

    if(!$product = Db::getRow($this->query(['id' => $id, 'groupby' => 'store_products.id']))) {
      return false;
    }
    
    if(!$generated = $this->generate($product, $price)) {
      return false;
    }

    return $generated;

  }


  /**
   * Prende prodotto da slug
   *
   * @param string $slug
   * @param array $filters
   * @return array|boolean
   */
  public function slug(string $slug, $filters = []):array|bool {

    if(!$product = Db::getRow($this->query(array_merge($filters,['slug' => $slug, 'groupby' => 'store_products.id'])))) {
      return false;
    }

    if(!$generated = $this->generate($product)) {
      return false;
    }

    return $generated;

  }




}

?>