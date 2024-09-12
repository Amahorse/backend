<?php


declare(strict_types=1);

namespace Kodelines\Oauth\Controllers;

use Kodelines\Db;
use Kodelines\Oauth\Client;
use Kodelines\Abstract\Controller;
use Kodelines\Tools\Str;
use Elements\Stores\Stores;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class ClientController extends Controller
{

  public function create(Request $request, Response $response) : Response {

    if(!empty($this->data['id_stores'])) {

      if(!$store = Stores::get($this->data['id_stores'])) {
        throw new HttpBadRequestException($request,'store_not_found');
      }

      $this->data['kid'] = Str::plain($store['name']) . '-' . Str::random(10);

    } else {

      $this->data['kid'] = Str::random(10);

    }

    $client = array_merge(Client::generate(),$this->data);

    if(!$client['id'] = Db::insert('oauth_clients',$client)) {
      throw new HttpBadRequestException($request,'database_error');
    }


    return $this->response($response,true);

  }


  public function update(Request $request, Response $response, $args) : Response {

  

    return $this->response($response,true);

  }



}