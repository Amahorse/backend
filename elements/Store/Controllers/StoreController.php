<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;
use Elements\Store\Helpers\Price;

class StoreController extends Controller {

  public $hidden = [];


  /**
   * Lista pulita di qualsiasi prodotto con parametri da postare in get
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function list(Request $request, Response $response) : Response {

    $data = [];

    foreach(Store::list($this->data) as $key => $value) {
      $data[] = array_merge($value,Price::calculate($value));
    }

    return $this->response($response,$data);

  }



  public function get(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    if(!$data = Store::get(id($args['id']),$this->data)) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,array_merge($data,Price::calculate($data,$this->data['quantity'])));

  }


  public function slug(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      $this->data['quantity'] = 1;
    }

    if(!$data = Store::slug($args['slug'],$this->data)) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,array_merge($data,Price::calculate($data,$this->data['quantity'])));

  }


  public function images(Request $request, Response $response,array $args) : Response {

    if(!$data = Store::getImages(id($args['id']))) {
      $data = [];
    }

    return $this->response($response,$data);

  }



}

?>