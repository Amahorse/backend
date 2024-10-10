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
    'neutral_image',
    'neutral_image_url',
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
    'info_care',
    'price_min',
    'price_max',
    'availability_total'
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
    'collection_current',
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

      //Calcolo prezzo riga
      $value = array_merge($value,Price::calculate($value));

      if(!isset($data[$value['code']])) {

        $data[$value['code']] = array_intersect_key($value, array_flip($this->visibleProduct));

        $data[$value['code']]['price_min'] = $value['total_to_pay'];

        $data[$value['code']]['price_max'] = $value['total_to_pay'];

        $data[$value['code']]['availability_total'] = 0;

        $data[$value['code']]['splits'] = [];

        $data[$value['code']]['categories'] = [];

      }

      if(!empty($value['split'])) {
        $varSplit = $value[$value['split'] . '_code'];
        $varOrder = $value[$value['split'] . '_order'];
      } else {
        $varSplit = 'none';
        $varOrder = 0;
      }

      //Variabile split settata
      if(!isset($data[$value['code']]['splits'][$varSplit])) {
        $data[$value['code']]['splits'][$varSplit] = [
          "code" => $varSplit,
          "order" => (int)$varOrder,
          "collection" => $value['collection'],
          "cover" => $value['cover'],
          "cover_url" => $value['cover_url'],
          "discount_percentage" => $value['discount_percentage'],
          "variants" => []
        ];
      }

      $data[$value['code']]['splits'][$varSplit]["variants"][$value['sku']] = array_intersect_key($value, array_flip($this->visibleVariant));

      //Controllo prezzo minimo e massimo per codice padre 
      if((float)$value['total_to_pay'] < (float)$data[$value['code']]['price_min']) {
        $data[$value['code']]['price_min'] = (float)$value['total_to_pay'];
      }

      if((float)$value['total_to_pay'] > (float)$data[$value['code']]['price_max']) {
        $data[$value['code']]['price_max'] = (float)$value['total_to_pay'];
      }

      //Controllo percentuale massima di sconto per variante
      if($value['discount_percentage'] > $data[$value['code']]['splits'][$varSplit]) {
        $data[$value['code']]['splits'][$varSplit]["discount_percentage"] = $value['discount_percentage'];
      }

      if(!empty($value['id_categories'])) {
        $data[$value['code']]['categories'][(int)$value['id_categories']] = ['id_categories' => $value['id_categories'], 'category' => $value['category']];
      }

      //Aggiungo disponibilità totale 
      $data[$value['code']]['availability_total'] += $value['availability'];
        
    }

    $values = array_values($data);

    foreach($values as $key => $value) {

      $values[$key]['categories'] = array_values($value['categories']);
      
      usort($values[$key]['splits'], function($a, $b) {
        return $a['order'] <=> $b['order'];
      });

      foreach($values[$key]['splits'] as $keySplit => $valueSplit) {

        // Ordina l'array per a1_order se la variabile $value['split'] è uguale a a0 e viceversa
        usort($values[$key]['splits'][$keySplit]['variants'], function($a, $b) use ($value) {
            if ($value['split'] === 'a0') {
              return $a['a1_order'] <=> $b['a1_order'];
            } else {
              return $a['a0_order'] <=> $b['a0_order'];
            }
        });

        // Se la variabile a4_order non è null, il secondo valore ulteriore per ordinamento è sempre a4_order
        usort($values[$key]['splits'][$keySplit]['variants'], function($a, $b) {
            return $a['a4_order'] <=> $b['a4_order'];
        });

      }
        

    }



    return $values;

  }

  public function explain(array $store):array {

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