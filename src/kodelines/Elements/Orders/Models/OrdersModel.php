<?php

declare(strict_types=1);

namespace Elements\Orders\Models;

use Kodelines\Abstract\Model;
use Kodelines\Helpers\Price;
use Elements\Resellers\Resellers;

class OrdersModel extends Model {

  public $table = 'store_orders';


  public function __construct() {

    parent::__construct();

    $this->defaults = [
      'status' => 'cart',
      'id_countries' => config('default','id_countries'),
      'type' => config('store','type'),
      'currency' => Price::$currency,
      'currency_conversion_rate' => Price::$conversionRate,
    ];

    //Se sono settate le costanti, metto queste come predefinite per evitare errori, comunque sono sovrascritte da array valori su funzioni create o update se necessario
    if(defined('_CLIENT_TYPE_')) {
      $this->defaults['type'] = _CLIENT_TYPE_;
    } 

    if(defined('_ID_RESELLERS_')) {
      $this->defaults['id_resellers'] = _ID_RESELLERS_;
    }

    if(defined('_ID_AGENTS_')) {
      $this->defaults['id_agents'] = _ID_AGENTS_;
    }


  }

  /**
   * Query the orders based on the given filters.
   *
   * @param array $filters The filters to apply to the query.
   * @return string The query result.
   */
  public function query($filters = []):string {


    $query = "
    SELECT
      store_orders.*,
      CASE WHEN store_orders.type = 'b2c' THEN CONCAT(u.first_name,' ',u.last_name) ELSE u.business_name END AS name,
      CASE WHEN u.email IS NULL THEN store_orders.email ELSE u.email END AS email_checkout,
      u.username,
      CASE WHEN u.language IS NULL THEN '".language()."' ELSE u.language END AS language,
      pay.id AS id_payments_incoming,
      pay.method AS payment_method,
      pay.modality AS payment_modality,
      COALESCE(pay.amount, (store_orders.total_to_pay + store_orders.total_shipping + store_orders.total_excise + store_orders.total_duties)) AS payment_amount,
      pay.date_payment,
      pay.transaction_id,
      pay.payer_id,
      COALESCE(pay.status, 'no') AS payment_status,
      pay.price_to_pay AS payment_price_to_pay,
      pay.tax AS payment_tax,
      pay.price_taxes AS payment_price_taxes,
      pay.price_taxes_excluded AS payment_price_taxes_excluded,
      pay.price_buy AS payment_price_buy,
      i.number AS invoice_number,
      i.file_pdf AS invoice_file_pdf,
      i.file_xsd AS invoice_file_xsd,
      i.file_xml AS invoice_file_xml,
      i.date_invoice,
      i.year AS invoice_year,
    ";

    // Il join sulla tabella tracking, nota di credito e preventivo lo faccio solo su backoffice
    if (_CONTEXT_ == 'admin') {
      $query .= "
      ic.file_pdf AS credit_note_file_pdf,
      ic.file_xsd AS credit_note_file_xsd,
      ic.file_xml AS credit_note_file_xml,
      quote.id AS quote_id,
      quote.number AS quote_number,
      quote.file_pdf AS quote_file_pdf,
      quote.year AS quote_year,
      quote.date_invoice AS date_quote,
      t.ref AS tracking_ref,
      t.browser AS tracking_browser,
      t.os AS tracking_os,
      t.language AS tracking_language,
      t.cpg AS tracking_cpg,
      t.ip AS tracking_ip, ";
    }

    $query .= "
      COALESCE(stores.name, '".config('app','name')."') AS store,
      (SELECT COALESCE(MAX(shipping_delay),0) FROM store_orders_products WHERE id_store_orders = store_orders.id) AS shipping_delay,
      COALESCE(stores.shipping_max_hour, ".encode(config('store','shipping_max_hour')).") AS shipping_max_hour,
      stores.pickup
    FROM
      store_orders 
      LEFT JOIN users u ON store_orders.id_users = u.id
      LEFT JOIN payments_incoming pay ON pay.id_store_orders = store_orders.id
      LEFT JOIN invoices i ON store_orders.id = i.id_store_orders AND (i.type = 'invoice_out' OR i.type = 'recepit')
    ";

    // TODO: il tracking si prende anche da oauth_tokens_jti, id_tracking rimane per vecchia gestione, andrebbe associato un jti fittizzio a vecchi tracking, tolto id e aggiornato jti anche degli ordini
    if (_CONTEXT_ == 'admin') {
      $query .= "
      LEFT JOIN invoices ic ON store_orders.id = ic.id_store_orders AND ic.type = 'credit_note'
      LEFT JOIN invoices quote ON store_orders.id = quote.id_store_orders AND quote.type = 'quote'
      LEFT JOIN tracking t ON store_orders.id_tracking = t.id        
      ";
    }

    $query .= "
      LEFT JOIN stores ON store_orders.id_stores = stores.id 
    WHERE store_orders.id IS NOT NULL
    ";

    // Status payment
    if (!empty($filters['payment_status'])) {
      if ($filters['payment_status'] == 'no') {
        $query .= " AND (pay.status = 'no' OR pay.status IS NULL) ";
      } else {
        $query .= " AND pay.status = " . encode($filters['payment_status']);
      }
    }

    // TODO: fare questa funzione di OR sui filtri di default per tutto
    if (!empty($filters['status']) && $filters['status'] == 'cart|pending') {
      $query .= " AND (store_orders.status = 'cart' OR store_orders.status = 'pending') ";
      unset($filters['status']);
    }

    // Ref
    if (_CONTEXT_ == 'admin' && !empty($filters['ref'])) {
      $query .= " AND t.ref = " . encode($filters['ref']);
    }

    // Date from
    if (!empty($filters['date_from'])) {
      $query .= " AND DATE(store_orders.date_order) >= " . encode($filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
      $query .= " AND DATE(store_orders.date_order) <= " . encode($filters['date_to']);
    }

    if (isset($filters['with_user'])) {
      $query .= " AND store_orders.id_users IS NOT NULL ";
    }

    // TODO: rimuovere se crea problemi
    if (empty($filters['groupby'])) {
      $filters['groupby'] = 'store_orders.id';
    }

    $filters = Resellers::addFilters($filters);

    $query .= $this->applyFilters($filters);

    return $query;
  
  
    }
  
  

}

?>