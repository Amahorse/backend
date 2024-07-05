<?php

declare(strict_types=1);

namespace Elements\Contacts;

use Slim\Routing\RouteCollectorProxy;
use Kodelines\Interfaces\FactoryInterface;
use Kodelines\Middleware\AuthMiddleware;

class Factory implements FactoryInterface
{

  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  public static function loadRoutes(RouteCollectorProxy $app): RouteCollectorProxy {

    $app->post('/contacts/requests', \Elements\Contacts\Controllers\RequestsController::class . ':create')->setName('contacts.requests.create');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->put('/contacts/requests/{id}', \Elements\Contacts\Controllers\RequestsController::class . ':update')->setName('contacts.requests.update');
  
      $api->delete('/contacts/requests/{id}', \Elements\Contacts\Controllers\RequestsController::class . ':delete')->setName('contacts.requests.delete'); 

    })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>