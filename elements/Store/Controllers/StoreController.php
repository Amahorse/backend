<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;

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

    $list = Store::list($this->data);

    return $this->response($response,$list);
  }



  public function get(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      Store::setQuantity((int)$this->data['quantity']);
    }


    if(!$data = Store::get(id($args['id']),$this->data)) {
      throw new HttpNotFoundException($request);
    }
   
    return $this->response($response,$data);

  }


  public function slug(Request $request, Response $response,array $args) : Response {

    if(!empty($this->data['quantity'])) {
      Store::setQuantity((int)$this->data['quantity']);
    }

    if(!$data = Store::slug($args['slug'],$this->data)) {
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