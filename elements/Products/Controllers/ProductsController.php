<?php

declare(strict_types=1);

namespace Elements\Products\Controllers;

use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ProductsController extends Controller {

 


  /**
   * Ritorna lista tipologie prodotto che sono diverse per tipo (vino, olio etc)
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function typologies(Request $request, Response $response, $args) : Response {

        $types = options('products','type');
    
        $data = array();
    
        if(isset($args['type'])) {

            $options = options('products_type_'.$args['type'],'typology');
    
            if(is_array($options)) {
      
              foreach($options as $typology => $name) {
                $data[] = array(
                  "typology" => $typology,
                  "name" => $name
                );
              }
      
          }
        } 
    
        return $this->response($response,$data);
    
  }

    /**
   * Ritorna lista tipologie prodotto che sono diverse per tipo (vino, olio etc)
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function organolectic_macroclassification(Request $request, Response $response, $args) : Response {

    $data = array();

    if(isset($args['type'])) {

        $options = options('products','organolectic_macroclassification');

        if(is_array($options)) {
  
          foreach($options as $organolectic_macroclassification => $name) {
            $data[] = array(
              "organolectic_macroclassification" => $organolectic_macroclassification,
              "name" => $name
            );
          }
  
      }
    } 

    return $this->response($response,$data);

}

}

?>