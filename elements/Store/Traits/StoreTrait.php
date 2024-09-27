<?php

namespace Elements\Store\Traits;

use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;
use Elements\Store\Helpers\Price;



trait StoreTrait {

  /**
   * Campi visibili array prodotto
   */
  public $visibleProduct = [
    'id',
    'code',
    'title',
    'slug',
    'brand',
    'brand_code',
    'family',
    'gender',
    'age',
    'type',
    'material',
    'tech',
    'season',
    'discipline',
    'a0',
    'a1',
    'a4',
    'split',
    'description',
    'content',
    'meta_title',
    'meta_description',
    'indexable',
    'size_fit',
    'tech_spech',
    'composition',
    'info_care'

  ];

  /**
   * Campi visibili array variante
   */
  public $visibleVariant = [
    'id_store_products',
    'sku',
    'variant',
    'status',
    'collection',
    'season',
    'color_primary',
    'color_secondary',
    'a0_code',
    'a0_description',
    'a0_order',
    'a1_code',
    'a1_description',
    'a1_order',
    'a4_code',
    'a4_description',
    'a4_order',
    'cover',
    'cover_url',
    'price',  
    'price_discount',
    'price_to_pay',
    'total_price',
    'total_discount',
    'discount_percentage',  
    'total_to_pay',
    'currency',
    'taxes_included',
    'availability',
    'minimum_order',
    
  ];

  public function split(array $store):array {

    $data = [];

    foreach($store as $value) {

      $value = array_merge($value,Price::calculate($value));
      
      if(!isset($data[$value['code']])) {

        $data[$value['code']] = array_intersect_key($value, array_flip($this->visibleProduct));

        $data[$value['code']]['variants'] = [];

        $data[$value['code']]['categories'] = [];

      }

      $data[$value['code']]['variants'][$value['sku']] = array_intersect_key($value, array_flip($this->visibleVariant));

      if(!empty($value['id_categories'])) {
        $data[$value['code']]['categories'][(int)$value['id_categories']] = ['id_categories' => $value['id_categories'], 'category' => $value['category']];
      }
        
    }

    $values = array_values($data);

    foreach($values as $key => $value) {
      $values[$key]['categories'] = array_values($value['categories']);
      $values[$key]['variants'] = array_values($value['variants']);
    }

    return $values;

  }


  /**
   * Lista pulita di qualsiasi prodotto con parametri da postare in get
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function list(Request $request, Response $response) : Response {


    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    if(!$data = Store::list(array_merge($this->data,$this->defaultFilters))) {
      return $this->response($response,[]);
    }
   
    return $this->response($response,$this->split($data));

  }


  public function get(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    $this->data['id_products'] = id($args['id']);

    if(!$data = Store::list(array_merge($this->data,$this->defaultFilters))) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,$this->split($data));

  }


  public function slug(Request $request, Response $response,array $args) : Response {

    
    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    $this->data['slug'] = $args['slug'];

    if(!$data = Store::list(array_merge($this->data,$this->defaultFilters))) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,$this->split($data));

  }



}

?>