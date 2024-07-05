<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;


use Kodelines\Abstract\Controller;
use Kodelines\Db;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Exception\ValidatorException;
use Elements\Store\Warehouse;
use Elements\Products\Models\ProductsModel;

class WarehouseController extends Controller {

  /**
   * Crea un elemento
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response {

  
    if($this->data['format'] == 'package' && empty($this->data['components'])) {
      throw new ValidatorException('packages_needs_components');
    }

  //Controllo se ci sono dati preset di prodotto, altrimenti li inserisco 
  if(empty($this->data['id_products'])) {

    $products = new ProductsModel;

    //Faccio un fix del campo nome che altrimenti sarebbe assente
    $this->data['name'] = $this->data[language()]['title'];

    $product = $products->create($this->data);

    $this->data['id_products'] = $product['id']; 

  }

  $object = $this->model->create($this->data);

  //Crei codice prodotto univoco in base a valori generati dal model e faccio update del database
  if(empty($object['code'])) {

      $object['code'] = Warehouse::generateCode($object['id'],$object['id_products'], $object['type'], $object['listing']);

      Db::update('store_products','code',$object['code'],'id',$object['id']);
  }
 
  return $this->response($response,[mb_strtolower($this->className) => $object]);
  
}



}

?>