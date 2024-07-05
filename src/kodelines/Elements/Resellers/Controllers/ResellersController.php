<?php

declare(strict_types=1);

namespace Elements\Resellers\Controllers;

use Kodelines\Db;
use Kodelines\Abstract\Controller;
use Elements\Data\Data;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class ResellersController extends Controller {

    public function create(Request $request, Response $response) : Response
    {
  
      parent::create($request,$response);

      $id = $this->model->object['id'];
  
      if(isset($this->data['configurator'])) {
        Db::insertMultiple($this->data['configurator'], 'configurator_warehouse_availability_resellers', 'id_resellers', id($id));
      }

      if(isset($this->data['store'])) {
        Db::insertMultiple($this->data['store'], 'store_products_resellers', 'id_resellers', id($id));
      }

      if(isset($this->data['data']) && Data::validate($this->data['data'],true)) {
        Data::set($id,'resellers',$this->data);
      }

      return $this->response($response,$this->model->object);
  
    }
  
    /**
     * Modifica utente
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function update(Request $request, Response $response, array $args) : Response
    {

        parent::update($request,$response,$args);

        if(isset($this->data['configurator'])) {
            Db::insertMultiple($this->data['configurator'], 'configurator_warehouse_availability_resellers', 'id_resellers', id($args['id']),\Elements\Configurator\Warehouse::reseller(id($args['id'])));
        }
    
        if(isset($this->data['store'])) {
            Db::insertMultiple($this->data['store'], 'store_products_resellers', 'id_resellers', id($args['id']),\Elements\Store\Warehouse::reseller(id($args['id'])));
        }
        

        if(isset($this->data['data']) && Data::validate($this->data['data'],true)) {

          //TODO: questa gestione è bruttissima, fare funzione che setta direttamente i main
          if($old = Data::getByElement('resellers',id($args['id']),'main')) {
            $id = $old['id'];
          } else {
            $id = 0;
          }

          Data::set(id($args['id']),'resellers',$this->data,$id);
        }
  

        return $this->response($response,$this->model->object);
    }
  

}
