<?php


declare(strict_types=1);

namespace Elements\Clients\Controllers;

use Kodelines\Helpers\Cache;
use Elements\Clients\Clients;
use Kodelines\Db;
use Kodelines\Abstract\Controller;
use Kodelines\Tools\Str;
use Elements\Stores\Stores;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class ClientsController extends Controller
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

    $client = array_merge(Clients::generate(),$this->data);

    if(!$client['id'] = Db::insert('oauth_clients',$client)) {
      throw new HttpBadRequestException($request,'database_error');
    }

    Cache::getInstance()->delete('oauth_clients');

    return $this->response($response,true);

  }


  public function update(Request $request, Response $response, $args) : Response {

    Cache::getInstance()->delete('oauth_clients');

    Cache::getInstance()->delete('oauth_origins_' . $args['id']);

    return $this->response($response,parent::update($request,$response,$args));

  }

  public function delete(Request $request, Response $response, $args) : Response {

    Cache::getInstance()->delete('oauth_clients');

    return $this->response($response,parent::delete($request,$response,$args));

  }


}