<?php

declare(strict_types=1);

namespace Elements\Stores\Controllers;

use Elements\Stores\Stores;
use Elements\Data\Data;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class StoresController extends Controller {

    public function create(Request $request, Response $response) : Response
    {
  
      parent::create($request,$response);

      $id = $this->model->object['id'];

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

        if(isset($this->data['data']) && Data::validate($this->data['data'],true)) {

          //TODO: questa gestione è bruttissima, fare funzione che setta direttamente i main
          if($old = Data::getByElement('stores',id($args['id']),'main')) {
            $id = $old['id'];
          } else {
            $id = 0;
          }

          Data::set(id($args['id']),'stores',$this->data,$id);

        }
  

        return $this->response($response,$this->model->object);
    }
  

}
