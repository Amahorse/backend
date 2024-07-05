<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Db;
use Kodelines\Exception\ValidatorException;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Store\Store;
use Elements\Store\Helpers\Package;
use Elements\Store\Controllers\WarehouseController;
use Elements\Store\Warehouse;
use Elements\Store\Models\WarehouseModel;

class PackagesController extends WarehouseController {

  public $index = '/store/packages';



  /**
   * Crea un elemento
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response {

  
    if(empty($this->data['components'])) {
      throw new ValidatorException('packages_needs_components');
    }

    $model = new WarehouseModel;

    $this->data['components'] = Package::fixComponents($this->data['components']);

    $object = $model->create($this->data);

    //Crei codice prodotto univoco in base a valori generati dal model e faccio update del database
    if(empty($object['code'])) {

        $object['code'] = Warehouse::generateCode($object['id'],$object['id'], 'packages', $object['listing']);

        Db::update('store_products','code',$object['code'],'id',$object['id']);
    }
  
    

    return $this->response($response,['packages' => $object]);
}


  /**
   * Crea un elemento
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function update(Request $request, Response $response, $args) : Response {

  
    if(empty($this->data['components'])) {
      throw new ValidatorException('packages_needs_components');
    }

    $model = new WarehouseModel;

    $this->data['components'] = Package::fixComponents($this->data['components']);

    $object = $model->update(id($args['id']),$this->data);

    

    return $this->response($response,['presets' => $object]);

}






}

?>