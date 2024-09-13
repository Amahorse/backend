<?php

namespace Elements\Store\Traits;

use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;


trait StoreTrait {

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
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,Store::split($data));


  }


  public function get(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    $this->data['id_products'] = id($args['id']);

    if(!$data = Store::list(array_merge($this->data,$this->defaultFilters))) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,Store::split($data));

  }


  public function slug(Request $request, Response $response,array $args) : Response {

    
    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    $this->data['slug'] = $args['slug'];

    if(!$data = Store::list(array_merge($this->data,$this->defaultFilters))) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,Store::split($data));

  }



}

?>