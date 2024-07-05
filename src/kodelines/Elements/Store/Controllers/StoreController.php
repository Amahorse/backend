<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;

class StoreController extends Controller {

  public $hidden = [
    "available_configurator",
    "available_quotes",
    "date_ins",
    "discount_global_minimum_order",
    "discount_product_minimum_order",
    "discounts_global_percentage",
    "discounts_product_percentage",
    "id_store_products_discounts",
    "id_store_tax",
    "id_stores",
    "label_included",
    "packaging",
    "price_buy",
    "price_agent_commission",
    "price_agent_commission_percentage",
    "price_capsule",
    "price_end_chain",
    "price_fee_end_chain",
    "price_free_port",
    "price_front_label",
    "price_packaging",
    "price_payment_commission",
    "price_processing",
    "price_recharge",
    "price_recharge_percentage",
    "price_reseller_fee",
    "price_reseller_fee_percentage",
    "price_reseller_recharge",
    "price_reseller_recharge_final",
    "price_reseller_recharge_percentage",
    "price_retro_label",
    "price_shipping_adjustment",
    "price_store_recharge",
    "price_store_recharge_final",
    "price_store_recharge_percentage",
    "price_vendor",
    "processing_included",
    "shelf_location",
    "show_manufacturer",
    "show_name_standard_label",
    "sync_error",
    "sync_last",
    "tax_multiplier",
    "tax_notes",
    "taxation_type",
    "total_agent_commission",
    "total_payment_commission",
    "total_price_vendor",
    "total_reseller_fee",
    "total_reseller_recharge"
];


  /**
   * Lista pulita di qualsiasi prodotto con parametri da postare in get
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function list(Request $request, Response $response) : Response {

    $list = Store::getInstance()->list($this->data);

    return $this->response($response,$list);
  }



  public function get(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      Store::getInstance()->setQuantity((int)$this->data['quantity']);
    }


    if(!$data = Store::getInstance()->get(id($args['id']),$this->data)) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,$data);

  }


  public function slug(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      Store::getInstance()->setQuantity((int)$this->data['quantity']);
    }

    if(!$data = Store::getInstance()->slug($args['slug'],$this->data)) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,$data);

  }

  public function images(Request $request, Response $response,array $args) : Response {

    if(!$data = \Elements\Store\Warehouse::getImages(id($args['id']))) {
      $data = [];
    }

    return $this->response($response,$data);

  }



}

?>