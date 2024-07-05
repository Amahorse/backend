<?php

declare(strict_types=1);

namespace Elements\Orders;

use Kodelines\Db;
use Kodelines\Tools\Str;
use Kodelines\Helpers\Price;

class Stats {


   /**
    * Somma valori dell'ordine
    *
    * @param array $orders
    * @return array
    */
   public static function sum(array $orders):array {

     $stats = array(
         'total' => 0,
         'total_price_taxes_excluded' => 0,
         'total_taxes' => 0,
         'total_to_pay' => 0,
         'total_amount' => 0,
         'total_quantity' => 0,
         'total_discount' => 0,
         'total_weight' => 0,
         'total_recharge' => 0,
         'average_price' => 0,
         'average_quantity' => 0,
         'average_discount' => 0,
         'total_agent_commission' => 0,
         'total_reseller_recharge' => 0
      );

     if(!is_array($orders)) {
       return $stats;
     }

     $stats['total'] = count($orders);

     foreach($orders as $order) {

       $stats['total_taxes'] += $order['total_taxes'];
       $stats['total_price_taxes_excluded'] += $order['total_price_taxes_excluded'];
       $stats['total_recharge'] += $order['total_recharge'];
       $stats['total_to_pay'] += $order['total_to_pay'];
       $stats['total_discount'] += $order['total_discount'];
       $stats['total_quantity'] += $order['total_quantity'];
       $stats['total_weight'] += $order['total_weight'];
       $stats['total_amount'] += $order['payment_amount'];
       $stats['total_agent_commission'] += $order['total_agent_commission'];
       $stats['total_reseller_recharge'] += $order['total_reseller_recharge'];
     }

     $stats = Price::formatMultiple($stats);


     if($stats['total'] > 0) {
       $stats['average_quantity'] = Price::format($stats['total_quantity'] / $stats['total']);
       $stats['average_discount'] = Price::format($stats['total_discount'] / $stats['total']);
     }


     return $stats;
   }

   /**
    * Ritorna statistiche ordini
    *
    * @param array $filters
    * @return array
    */
   public static function orders($filters = array()):array {

    $query = "
    SELECT
      CASE WHEN t.ref IS NULL THEN 'unknown' ELSE t.ref END AS ref,
      CASE WHEN t.cpg IS NULL THEN 'unknown' ELSE t.cpg END AS cpg,
      CASE WHEN t.os IS NULL THEN 'unknown' ELSE t.os END AS os,
      CASE WHEN t.browser IS NULL THEN 'unknown' ELSE t.browser END AS browser,
      CASE WHEN t.language IS NULL THEN 'unknown' ELSE t.language END AS language,
      o.*,
      u.id AS id_users
    FROM store_orders o
      LEFT JOIN tracking t ON t.id = o.id_tracking
      LEFT JOIN users u ON t.id = u.id_tracking
    WHERE o.id IS NOT NULL
    ";

    $query .= self::filters($filters);


    $stats = array();

    $items = Db::getArray($query);

    foreach($items as $item) {

      //Orders by type and type
      if(!isset($stats[$item['status']])) {

        $stats[$item['status']] = array(
          "items" => array(
            "orders" => array(),
            "products" => array()
          ),
          "cash" => array(
            "value" => array()
          ),
          "counter" => 0,
          "total_price_final" => 0,
          "total_to_pay" => 0,
          "total_recharge" => 0,
          "total_products" => 0,
          "ref" => array(),
          "cpg" => array(),
          "product" => array(),
          "product_type" => array(),
          "region" => array()
        );

      }

      //Referral
      if(!isset($stats[$item['status']]['ref'][$item['ref']])) {
        $stats[$item['status']]['ref'][$item['ref']] = 0;
      }

      $stats[$item['status']]['ref'][$item['ref']]++;

      //Campaign
      if(!isset($stats[$item['status']]['cpg'][$item['cpg']])) {
        $stats[$item['status']]['cpg'][$item['cpg']] = 0;
      }

      $stats[$item['status']]['cpg'][$item['cpg']]++;

      //Type
      if(!isset($stats[$item['status']]['type'][$item['type']])) {
        $stats[$item['status']]['type'][$item['type']] = 0;
      }

      $stats[$item['status']]['type'][$item['type']]++;

      //Counters

      $stats[$item['status']]['counter'] += 1;

      $stats[$item['status']]['total_products'] += $item["total_products"];

      $stats[$item['status']]['total_to_pay'] += $item["total_to_pay"];

      $stats[$item['status']]['total_price_final'] += $item["total_price_final"];

      $stats[$item['status']]['total_recharge'] += $item["total_recharge"];

      $stats[$item['status']]['items']['orders'][] = array('date_order' => $item['date_order']);

      $stats[$item['status']]['items']['products'][] = array('date_order' => $item['date_order'], 'increment' => $item['total_products']);

      $stats[$item['status']]['cash']['value'][] = array('date_order' => $item['date_order'], 'increment' => $item['total_price_final']);

    }

    return $stats;
   }

     /**
    * Ritorna statistiche prodotti ordinati
    *
    * @param array $filters
    * @return array
    */
    public static function products($filters = array()):array {

      $query = "
      SELECT
         CASE WHEN t.ref IS NULL THEN 'unknown' ELSE t.ref END AS ref,
         CASE WHEN t.cpg IS NULL THEN 'unknown' ELSE t.cpg END AS cpg,
         CASE WHEN t.os IS NULL THEN 'unknown' ELSE t.os END AS os,
         CASE WHEN t.browser IS NULL THEN 'unknown' ELSE t.browser END AS browser,
         CASE WHEN t.language IS NULL THEN 'unknown' ELSE t.language END AS language,
         p.*,
         o.status,
         u.id AS id_users
       FROM
    		 	store_orders_products p
    		 	JOIN store_orders o ON p.id_store_orders = o.id
             LEFT JOIN tracking t ON t.id = o.id_tracking
             LEFT JOIN users u ON t.id = u.id_tracking
             LEFT JOIN store_products sp ON av.id_store_products = sp.id
             LEFT JOIN countries_regions geo ON geo.id = pr.id_countries_regions
    		WHERE pr.name IS NOT NULL
      ";

      $query .= self::filters($filters);

      $stats = array();

        //TODO: da capire se questo va bene, andrebbero rifatte per bene 
        $products = Db::getArray($query);

        foreach($products as $item) {

          $stats[$item['status']] = array(
            "items" => array(
              "orders" => array(),
              "products" => array()
            ),
            "cash" => array(
              "value" => array()
            ),
            "counter" => 0,
            "total_price_final" => 0,
            "total_to_pay" => 0,
            "total_recharge" => 0,
            "total_products" => 0,
            "ref" => array(),
            "cpg" => array(),
            "product" => array(),
            "product_type" => array(),
            "region" => array()
          );
  

          //Product name
          if(!isset($stats[$item['status']]['product'][Str::plain($item['name'])])) {
            $stats[$item['status']]['product'][Str::plain($item['name'])] = 0;
          }

          $stats[$item['status']]['product'][Str::plain($item['name'])] += $item['quantity'];

          //Product type
          if(!isset($stats[$item['status']]['product_type'][$item['product_type']])) {
            $stats[$item['status']]['product_type'][$item['product_type']] = 0;
          }

          $stats[$item['status']]['product_type'][$item['product_type']]++;

          //Region
          if(!isset($stats[$item['status']]['region'][$item['region']])) {
            $stats[$item['status']]['region'][$item['region']] = 0;
          }

          $stats[$item['status']]['region'][$item['region']]++;

        }
  
  
      return $stats;
     }
  
     /**
      * Applica filtri a tutte le query sia prodotti che ordini
      *
      * @param array $filters
      * @return string
      */
     public static function filters($filters = []):string {

      $query = "";

      if(!empty($filters['ref'])) {

        if($filters['ref'] == 'unknown') {
          $query .= " AND t.ref IS NULL";
        } else {
          $query .= " AND t.ref = " . encode($filters['ref']);
        }

      }

      if(!empty($filters['cpg'])) {

        if($filters['cpg'] == 'unknown') {
          $query .= " AND t.cpg IS NULL";
        } else {
          $query .= " AND t.cpg = " . encode($filters['cpg']);
        }

      }

      if(!empty($filters['id_agents'])) {

         $query .= " AND o.id_agents = " . id($filters['id_agents']);

      }

      //Date from
      if(!empty($filters['date_from'])) {
        $query .= " AND DATE(o.date_order) >= DATE(" . encode($filters['date_from']) . ")";
      }

      if(!empty($filters['date_to'])) {
        $query .= " AND DATE(o.date_order) <= DATE(" . encode($filters['date_to']) . ")";
      }

      //Type
      if(!empty($filters['type'])) {
        $query .= " AND o.type = " . encode($filters['type']);
      }

      $query .= "  ORDER BY o.date_order ASC";


      return $query;

     }


}

?>