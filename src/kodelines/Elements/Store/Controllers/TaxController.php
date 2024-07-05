<?php

declare(strict_types=1);

namespace Elements\Store\Controllers;

use Kodelines\Abstract\Controller;
use Kodelines\Db;
use Kodelines\Helpers\Countries;
use Kodelines\Tools\Str;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Elements\Store\Tax;
use Slim\Exception\HttpNotFoundException;

class TaxController extends Controller {

  /**
   * Crea un elemento
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response {


        $this->data['group_uniq'] = Str::random(10);

        if(!empty($this->data['community'])) {

          $main = 0;

          foreach(Countries::getByCommunity($this->data['community']) as $country) {

            if($main == 0) {
              $this->data['main'] = 1;
              $main = 1;
            } else {
              $this->data['main'] = 0;
            }

            $this->data['id_countries'] = $country['id'];

            //Insert values on db
            Db::replace('store_tax',$this->data);

          }

        } else {

          $this->data['main'] = 1;

          //Insert values on db
          Db::replace('store_tax',$this->data);
           
  
        }

      

      return $this->response($response,[mb_strtolower($this->className) => $this->data]);
  }



    /**
   * Aggiorna elemento da id
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function update(Request $request, Response $response,array $args) : Response{


        if(!$tax = Tax::get(id($args['id']))) {
          throw new HttpNotFoundException($request);
        }

        if(!empty($this->data['community'])) {

          foreach(Tax::getByGroup($tax['group_uniq']) as $country) {

            $this->data['id_countries'] = $country['id_countries'];

            //Insert values on db
            Db::updateArray('store_tax',$this->data, 'id', $country['id']);

          }

        } else {

          //Insert values on db
          Db::updateArray('store_tax',$this->data,'id',$tax['id']);

        }

      

      return $this->response($response,[mb_strtolower($this->className) => $this->data]);

  }





}

?>